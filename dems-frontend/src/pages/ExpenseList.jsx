import { useState, useEffect } from 'react';
import { PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import api from '../services/api';

const CATEGORIES = ['Food', 'Transportation', 'Entertainment', 'Shopping', 'Utilities', 'Health', 'Other'];

const CAT_COLORS = {
  Food:           'bg-blue-100 text-blue-600',
  Transportation: 'bg-cyan-100 text-cyan-600',
  Entertainment:  'bg-purple-100 text-purple-600',
  Shopping:       'bg-pink-100 text-pink-600',
  Utilities:      'bg-orange-100 text-orange-600',
  Health:         'bg-green-100 text-green-600',
  Other:          'bg-gray-100 text-gray-600',
};

function EditModal({ expense, onClose, onSave }) {
  const [form, setForm]   = useState({ ...expense, date: expense.date?.substring(0,10) });
  const [loading, setLoading] = useState(false);

  const handleSave = async () => {
    setLoading(true);
    try {
      const res = await api.put(`/expenses/${expense.id}`, form);
      onSave(res.data);
    } catch {} finally { setLoading(false); }
  };

  return (
    <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div className="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h3 className="text-lg font-semibold mb-5">Edit Expense</h3>
        <div className="space-y-4">
          <input type="number" step="0.01" value={form.amount} onChange={e => setForm({...form, amount: e.target.value})}
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Amount" />
          <select value={form.category} onChange={e => setForm({...form, category: e.target.value})}
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            {CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
          </select>
          <input type="date" value={form.date} onChange={e => setForm({...form, date: e.target.value})}
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <textarea value={form.description || ''} onChange={e => setForm({...form, description: e.target.value})}
            className="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" rows={3} placeholder="Description" />
        </div>
        <div className="flex gap-3 mt-5">
          <button onClick={handleSave} disabled={loading}
            className="flex-1 py-2.5 bg-gray-900 text-white font-medium rounded-xl hover:bg-gray-800 disabled:opacity-60">
            {loading ? 'Saving...' : 'Save Changes'}
          </button>
          <button onClick={onClose} className="px-5 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50">Cancel</button>
        </div>
      </div>
    </div>
  );
}

export default function ExpenseList() {
  const [expenses, setExpenses] = useState([]);
  const [search, setSearch]     = useState('');
  const [loading, setLoading]   = useState(true);
  const [editing, setEditing]   = useState(null);

  const fetchExpenses = async (q = '') => {
    setLoading(true);
    const res = await api.get('/expenses', { params: { search: q } });
    setExpenses(res.data);
    setLoading(false);
  };

  useEffect(() => { fetchExpenses(); }, []);

  useEffect(() => {
    const timer = setTimeout(() => fetchExpenses(search), 400);
    return () => clearTimeout(timer);
  }, [search]);

  const handleDelete = async (id) => {
    if (!confirm('Delete this expense?')) return;
    await api.delete(`/expenses/${id}`);
    setExpenses(prev => prev.filter(e => e.id !== id));
  };

  const handleSaved = (updated) => {
    setExpenses(prev => prev.map(e => e.id === updated.id ? updated : e));
    setEditing(null);
  };

  const formatDate = (d) => new Date(d).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });

  return (
    <div className="p-8">
      <div className="mb-8 pb-4 border-b border-gray-100">
        <h1 className="text-3xl font-bold text-gray-900">Expense List</h1>
        <p className="text-gray-500 text-sm mt-1">View and manage all expenses</p>
      </div>

      <div className="bg-white rounded-xl border border-gray-100">
        {/* Table Header */}
        <div className="flex items-center justify-between p-5 border-b border-gray-100">
          <h2 className="font-semibold text-gray-900">All Expenses</h2>
          <div className="relative">
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
            <input
              type="text"
              value={search}
              onChange={e => setSearch(e.target.value)}
              placeholder="Search expenses..."
              className="pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-56"
            />
          </div>
        </div>

        {/* Table */}
        {loading ? (
          <div className="flex items-center justify-center py-16">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <table className="w-full">
            <thead>
              <tr className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th className="px-6 py-3">Date</th>
                <th className="px-6 py-3">Description</th>
                <th className="px-6 py-3">Category</th>
                <th className="px-6 py-3 text-right">Amount</th>
                <th className="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {expenses.map((exp) => (
                <tr key={exp.id} className="border-t border-gray-50 hover:bg-gray-50/50 transition-colors">
                  <td className="px-6 py-4 text-sm text-gray-600">{formatDate(exp.date)}</td>
                  <td className="px-6 py-4 text-sm text-gray-900">{exp.description || '—'}</td>
                  <td className="px-6 py-4">
                    <span className={`px-2.5 py-1 text-xs font-medium rounded-full ${CAT_COLORS[exp.category] || CAT_COLORS.Other}`}>
                      {exp.category}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm font-semibold text-red-500 text-right">
                    ${parseFloat(exp.amount).toFixed(2)}
                  </td>
                  <td className="px-6 py-4 text-right">
                    <button onClick={() => setEditing(exp)} className="text-gray-400 hover:text-blue-500 mr-3 transition-colors">
                      <PencilIcon className="w-4 h-4" />
                    </button>
                    <button onClick={() => handleDelete(exp.id)} className="text-gray-400 hover:text-red-500 transition-colors">
                      <TrashIcon className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
              {expenses.length === 0 && (
                <tr><td colSpan={5} className="px-6 py-16 text-center text-gray-400 text-sm">No expenses found</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {editing && <EditModal expense={editing} onClose={() => setEditing(null)} onSave={handleSaved} />}
    </div>
  );
}