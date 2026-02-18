import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const CATEGORIES = ['Food', 'Transportation', 'Entertainment', 'Shopping', 'Utilities', 'Health', 'Other'];

export default function AddExpense() {
  const navigate = useNavigate();
  const [form, setForm]     = useState({ amount: '', category: '', date: '', description: '' });
  const [error, setError]   = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await api.post('/expenses', form);
      setSuccess('Expense saved successfully!');
      setForm({ amount: '', category: '', date: '', description: '' });
      setTimeout(() => navigate('/expense-list'), 1200);
    } catch (err) {
      const errs = err.response?.data?.errors;
      setError(errs ? Object.values(errs).flat().join(', ') : 'Failed to save expense');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8 pb-4 border-b border-gray-100">
        <h1 className="text-3xl font-bold text-gray-900">Add Expense</h1>
        <p className="text-gray-500 text-sm mt-1">Record a new expense</p>
      </div>

      <div className="max-w-2xl">
        <div className="bg-white rounded-xl p-6 border border-gray-100">
          <h2 className="text-base font-semibold text-gray-900 mb-6">Expense Details</h2>

          {error   && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">{error}</div>}
          {success && <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">{success}</div>}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1.5">Amount ($)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0.01"
                  value={form.amount}
                  onChange={e => setForm({...form, amount: e.target.value})}
                  placeholder="0.00"
                  className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select
                  value={form.category}
                  onChange={e => setForm({...form, category: e.target.value})}
                  className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select category</option>
                  {CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
                </select>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Date</label>
              <input
                type="date"
                value={form.date}
                onChange={e => setForm({...form, date: e.target.value})}
                className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
              <textarea
                value={form.description}
                onChange={e => setForm({...form, description: e.target.value})}
                placeholder="Enter expense description..."
                rows={4}
                className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              />
            </div>

            <div className="flex gap-3 pt-2">
              <button
                type="submit"
                disabled={loading}
                className="flex-1 py-3 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl transition-colors disabled:opacity-60"
              >
                {loading ? 'Saving...' : 'Save Expense'}
              </button>
              <button
                type="button"
                onClick={() => navigate('/expense-list')}
                className="px-6 py-3 border border-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}