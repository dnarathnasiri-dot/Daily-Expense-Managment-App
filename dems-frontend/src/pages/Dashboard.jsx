
import { useState, useEffect, useRef } from 'react';
import { Chart, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'react-chartjs-2';
import api from '../services/api';

Chart.register(ArcElement, Tooltip, Legend);

const CATEGORY_COLORS = {
  Food:           '#3b82f6',
  Transportation: '#22c55e',
  Entertainment:  '#f59e0b',
  Shopping:       '#ef4444',
  Utilities:      '#8b5cf6',
  Health:         '#06b6d4',
  Other:          '#6b7280',
};

function StatCard({ label, value, icon, valueClass = '' }) {
  return (
    <div className="bg-white rounded-xl p-5 border border-gray-100 flex items-center justify-between">
      <div>
        <p className="text-sm text-gray-500 mb-1">{label}</p>
        <p className={`text-2xl font-bold ${valueClass || 'text-gray-900'}`}>{value}</p>
      </div>
      <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-2xl">{icon}</div>
    </div>
  );
}

export default function Dashboard() {
  const [data, setData]       = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/dashboard').then(r => { setData(r.data); setLoading(false); });
  }, []);

  if (loading) return (
    <div className="p-8 flex items-center justify-center h-screen">
      <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>
  );

  const categories   = data?.by_category || [];
  const total        = categories.reduce((s, c) => s + c.total, 0);
  const pieLabels    = categories.map(c => `${c.category} ${total ? Math.round((c.total/total)*100) : 0}%`);
  const pieValues    = categories.map(c => c.total);
  const pieColors    = categories.map(c => CATEGORY_COLORS[c.category] || '#6b7280');

  const pieData = {
    labels: pieLabels,
    datasets: [{ data: pieValues, backgroundColor: pieColors, borderWidth: 2, borderColor: '#fff' }],
  };

  const pieOptions = {
    responsive: true,
    plugins: {
      legend: { position: 'right', labels: { font: { size: 12 }, padding: 12 } },
    },
  };

  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8 pb-4 border-b border-gray-100">
        <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-500 text-sm mt-1">Overview of your expenses</p>
      </div>

      {/* Stat Cards */}
      <div className="grid grid-cols-4 gap-4 mb-8">
        <StatCard label="Today's Expenses"    value={`$${data?.today_expenses?.toFixed(2) || '0.00'}`}  icon="📅" valueClass="text-red-500" />
        <StatCard label="This Month"          value={`$${data?.month_expenses?.toFixed(2) || '0.00'}`}  icon="📉" valueClass="text-red-500" />
        <StatCard label="Total Transactions"  value={data?.total_transactions || 0}                      icon="💲" />
        <StatCard label="Average Expense"     value={`$${data?.avg_expense?.toFixed(2) || '0.00'}`}     icon="$" />
      </div>

      {/* Charts + Recent */}
      <div className="grid grid-cols-2 gap-6">
        {/* Pie Chart */}
        <div className="bg-white rounded-xl p-6 border border-gray-100">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Expenses by Category</h2>
          {categories.length > 0
            ? <Pie data={pieData} options={pieOptions} />
            : <p className="text-gray-400 text-sm text-center py-12">No expenses yet</p>
          }
        </div>

        {/* Recent Transactions */}
        <div className="bg-white rounded-xl p-6 border border-gray-100">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h2>
          <div className="space-y-3">
            {data?.recent?.map((exp) => (
              <div key={exp.id} className="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                  <p className="text-sm font-medium text-gray-900">{exp.description || exp.category}</p>
                  <p className="text-xs text-gray-400">
                    {exp.category} &bull; {new Date(exp.date).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })}
                  </p>
                </div>
                <span className="text-sm font-semibold text-red-500">-${parseFloat(exp.amount).toFixed(2)}</span>
              </div>
            ))}
            {(!data?.recent || data.recent.length === 0) && (
              <p className="text-gray-400 text-sm text-center py-8">No recent transactions</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
