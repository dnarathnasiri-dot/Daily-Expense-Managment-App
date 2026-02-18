import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

function Toggle({ checked, onChange }) {
  return (
    <button
      onClick={() => onChange(!checked)}
      className={`relative w-12 h-6 rounded-full transition-colors ${checked ? 'bg-gray-800' : 'bg-gray-200'}`}
    >
      <span className={`absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform ${checked ? 'translate-x-6' : ''}`} />
    </button>
  );
}

export default function Settings() {
  const { user, logout, updateUser } = useAuth();
  const navigate = useNavigate();

  const [profile, setProfile]   = useState({ first_name: user?.first_name || '', last_name: user?.last_name || '', email: user?.email || '' });
  const [passwords, setPasswords] = useState({ current_password: '', new_password: '', new_password_confirmation: '' });
  const [notifs, setNotifs]     = useState({ email_notifications: user?.email_notifications ?? true, daily_summary: user?.daily_summary ?? false, budget_alerts: user?.budget_alerts ?? true });

  const [profileMsg, setProfileMsg]   = useState({ text: '', ok: true });
  const [passMsg,    setPassMsg]      = useState({ text: '', ok: true });
  const [notifMsg,   setNotifMsg]     = useState({ text: '', ok: true });
  const [saving,     setSaving]       = useState('');

  const saveProfile = async () => {
    setSaving('profile');
    try {
      const res = await api.put('/settings/profile', profile);
      updateUser(res.data.user);
      setProfileMsg({ text: 'Profile updated!', ok: true });
    } catch (e) {
      setProfileMsg({ text: e.response?.data?.message || 'Error', ok: false });
    } finally { setSaving(''); setTimeout(() => setProfileMsg({ text: '' }), 3000); }
  };

  const savePassword = async () => {
    setSaving('pass');
    try {
      await api.put('/settings/password', passwords);
      setPassMsg({ text: 'Password updated!', ok: true });
      setPasswords({ current_password: '', new_password: '', new_password_confirmation: '' });
    } catch (e) {
      setPassMsg({ text: e.response?.data?.message || 'Error', ok: false });
    } finally { setSaving(''); setTimeout(() => setPassMsg({ text: '' }), 3000); }
  };

  const saveNotifs = async (key, val) => {
    const updated = { ...notifs, [key]: val };
    setNotifs(updated);
    try {
      await api.put('/settings/notifications', updated);
      setNotifMsg({ text: 'Saved!', ok: true });
    } catch { setNotifMsg({ text: 'Error', ok: false }); }
    setTimeout(() => setNotifMsg({ text: '' }), 2000);
  };

  const handleExport = () => {
    const token = localStorage.getItem('dems_token');
    window.open(`http://localhost:8000/api/settings/export?token=${token}`, '_blank');
  };

  const handleDelete = async () => {
    if (!confirm('Are you sure? This will permanently delete your account and all data.')) return;
    await api.delete('/settings/account');
    await logout();
    navigate('/login');
  };

  const inputCls = "w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500";

  return (
    <div className="p-8">
      <div className="mb-8 pb-4 border-b border-gray-100">
        <h1 className="text-3xl font-bold text-gray-900">Settings</h1>
        <p className="text-gray-500 text-sm mt-1">Manage your account and preferences</p>
      </div>

      <div className="max-w-2xl space-y-6">

        {/* Profile */}
        <section className="bg-white rounded-xl p-6 border border-gray-100">
          <div className="flex items-center gap-2 mb-5">
            <span className="text-blue-600">👤</span>
            <h2 className="font-semibold text-gray-900">Profile Settings</h2>
          </div>
          {profileMsg.text && (
            <div className={`mb-4 p-3 rounded-lg text-sm ${profileMsg.ok ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
              {profileMsg.text}
            </div>
          )}
          <div className="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
              <input type="text" className={inputCls} value={profile.first_name} onChange={e => setProfile({...profile, first_name: e.target.value})} />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
              <input type="text" className={inputCls} value={profile.last_name} onChange={e => setProfile({...profile, last_name: e.target.value})} />
            </div>
          </div>
          <div className="mb-5">
            <label className="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <input type="email" className={inputCls} value={profile.email} onChange={e => setProfile({...profile, email: e.target.value})} />
          </div>
          <button onClick={saveProfile} disabled={saving === 'profile'}
            className="px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 disabled:opacity-60">
            {saving === 'profile' ? 'Saving...' : 'Save Changes'}
          </button>
        </section>

        {/* Notifications */}
        <section className="bg-white rounded-xl p-6 border border-gray-100">
          <div className="flex items-center gap-2 mb-5">
            <span className="text-blue-600">🔔</span>
            <h2 className="font-semibold text-gray-900">Notifications</h2>
            {notifMsg.text && <span className={`ml-auto text-xs ${notifMsg.ok ? 'text-green-600' : 'text-red-600'}`}>{notifMsg.text}</span>}
          </div>
          {[
            { key: 'email_notifications', label: 'Email Notifications', desc: 'Receive expense alerts via email' },
            { key: 'daily_summary',       label: 'Daily Summary',       desc: 'Get daily expense summary reports' },
            { key: 'budget_alerts',       label: 'Budget Alerts',       desc: 'Alert when approaching budget limits' },
          ].map(({ key, label, desc }) => (
            <div key={key} className="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
              <div>
                <p className="text-sm font-medium text-gray-900">{label}</p>
                <p className="text-xs text-gray-500 mt-0.5">{desc}</p>
              </div>
              <Toggle checked={notifs[key]} onChange={(val) => saveNotifs(key, val)} />
            </div>
          ))}
        </section>

        {/* Security */}
        <section className="bg-white rounded-xl p-6 border border-gray-100">
          <div className="flex items-center gap-2 mb-5">
            <span className="text-blue-600">🛡</span>
            <h2 className="font-semibold text-gray-900">Security</h2>
          </div>
          {passMsg.text && (
            <div className={`mb-4 p-3 rounded-lg text-sm ${passMsg.ok ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
              {passMsg.text}
            </div>
          )}
          <div className="space-y-4 mb-5">
            {[
              { key: 'current_password', label: 'Current Password' },
              { key: 'new_password',     label: 'New Password' },
              { key: 'new_password_confirmation', label: 'Confirm New Password' },
            ].map(({ key, label }) => (
              <div key={key}>
                <label className="block text-sm font-medium text-gray-700 mb-1.5">{label}</label>
                <input type="password" className={inputCls} value={passwords[key]}
                  onChange={e => setPasswords({...passwords, [key]: e.target.value})} />
              </div>
            ))}
          </div>
          <button onClick={savePassword} disabled={saving === 'pass'}
            className="px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 disabled:opacity-60">
            {saving === 'pass' ? 'Updating...' : 'Update Password'}
          </button>
        </section>

        {/* Data Management */}
        <section className="bg-white rounded-xl p-6 border border-gray-100">
          <div className="flex items-center gap-2 mb-5">
            <span className="text-blue-600">🗄</span>
            <h2 className="font-semibold text-gray-900">Data Management</h2>
          </div>

          <div className="flex items-center justify-between py-3 border-b border-gray-100">
            <div>
              <p className="text-sm font-medium text-gray-900">Export Data</p>
              <p className="text-xs text-gray-500 mt-0.5">Download all your expense data</p>
            </div>
            <button onClick={handleExport}
              className="px-4 py-2 border border-gray-200 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
              Export CSV
            </button>
          </div>

          <div className="flex items-center justify-between py-3 mt-2 bg-red-50 rounded-xl px-4">
            <div>
              <p className="text-sm font-medium text-red-600">Delete Account</p>
              <p className="text-xs text-red-400 mt-0.5">Permanently delete your account and data</p>
            </div>
            <button onClick={handleDelete}
              className="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-colors">
              Delete
            </button>
          </div>
        </section>

      </div>
    </div>
  );
}