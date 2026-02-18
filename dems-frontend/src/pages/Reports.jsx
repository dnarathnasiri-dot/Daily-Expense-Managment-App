import { useState, useEffect } from 'react';
import { Chart, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend, Title } from 'chart.js';
import { Bar, Pie } from 'react-chartjs-2';
import api from '../services/api';

Chart.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend, Title);

const CATEGORY_COLORS = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#6b7280'];

export default function Reports() {
  const [summary,  setSummary]  = useState(null);
  const [daily,    setDaily]    = useState([]);
  const [catData,  setCatData]  = useState([]);
  const [monthly,  setMonthly]  = useState([]);
  const [loading,  setLoading]  = useState(true);

  useEffect(() => {
    Promise.all([
      api.get('/reports/summary'),
      api.get('/reports/daily', { params: { days: 7 } }),
      api.get('/reports/category'),
      api.get('/reports/monthly'),
    ]).then(([s, d, c, m]) => {
      setSummary(s.data);
      setDaily(d.data);
      setCatData(c.data);
      setMonthly(m.data);
      setLoading(false);
    });
  }, []);

  if (loading) return (
    <div className="p-8 flex items-center justify-center h-screen">
      <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>
  );

  // --- Bar chart (Daily) ---
  const barData = {
    labels: daily.map(d => new Date(d.day).toLocaleDateString('en-US', { month:'short', day:'numeric' })),
    datasets: [{
      label: 'Daily Expenses',
      data: daily.map(d => d.total),
      backgroundColor: '#3b82f6',
      borderRadius: 6,
    }],
  };
  const barOptions = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
  };

  // --- Pie chart (Category) ---
  const totalCat = catData.reduce((s, c) => s + c.total, 0);
  const pieData = {
    labels: catData.map(c => `${c.category} (${totalCat ? Math.round((c.total/totalCat)*100) : 0}%)`),
    datasets: [{ data: catData.map(c => c.total), backgroundColor: CATEGORY_COLORS, borderWidth: 2, borderColor: '#fff' }],
  };
  const pieOptions = {
    responsive: true,
    plugins: { legend: { position: 'right', labels: { font: { size: 11 }, padding: 10 } } },
  };

  // Monthly max for progress bars
  const maxMonthly = monthly.length ? Math.max(...monthly.map(m => m.total)) : 1;

  return (
    <div className="p-8">
      <div className="mb-8 pb-4 border-b border-gray-100">
        <h1 className="text-3xl font-bold text-gray-900">Reports</h1>
        <p className="text-gray-500 text-sm mt-1">Analyze your spending patterns</p>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-3 gap-4 mb-6">
        {[
          { label: 'Total Expenses',        value: `$${summary?.total_expenses?.toFixed(2)}`,      red: true },
          { label: 'Average per Transaction', value: `$${summary?.avg_per_transaction?.toFixed(2)}` },
          { label: 'Total Categories',      value: summary?.total_categories },
        ].map(({ label, value, red }) => (
          <div key={label} className="bg-white rounded-xl p-5 border border-gray-100">
            <p className="text-sm text-gray-500 mb-1">{label}</p>
            <p className={`text-3xl font-bold ${red ? 'text-red-500' : 'text-gray-900'}`}>{value}</p>
          </div>
        ))}
      </div>

      {/* Bar Chart */}
      <div className="bg-white rounded-xl p-6 border border-gray-100 mb-6">
        <h2 className="text-base font-semibold text-gray-900 mb-4">Daily Expenses (Last 7 Days)</h2>
        {daily.length > 0
          ? <Bar data={barData} options={barOptions} />
          : <p className="text-gray-400 text-sm text-center py-12">No data available</p>}
      </div>

      {/* Pie + Monthly */}
      <div className="grid grid-cols-2 gap-6">
        <div className="bg-white rounded-xl p-6 border border-gray-100">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Expenses by Category</h2>
          {catData.length > 0
            ? <Pie data={pieData} options={pieOptions} />
            : <p className="text-gray-400 text-sm text-center py-12">No data available</p>}
        </div>

        <div className="bg-white rounded-xl p-6 border border-gray-100">
          <h2 className="text-base font-semibold text-gray-900 mb-4">Monthly Breakdown</h2>
          <div className="space-y-4">
            {monthly.map((m) => {
              const label = new Date(m.month + '-01').toLocaleDateString('en-US', { month:'short', year:'numeric' });
              const pct   = Math.round((m.total / maxMonthly) * 100);
              return (
                <div key={m.month}>
                  <div className="flex justify-between text-sm mb-1.5">
                    <span className="text-gray-700 font-medium">{label}</span>
                    <span className="text-gray-900 font-semibold">${parseFloat(m.total).toFixed(2)}</span>
                  </div>
                  <div className="h-2 bg-gray-100 rounded-full">
                    <div className="h-2 bg-blue-500 rounded-full" style={{ width: `${pct}%` }} />
                  </div>
                </div>
              );
            })}
            {monthly.length === 0 && <p className="text-gray-400 text-sm text-center py-8">No data available</p>}
          </div>
        </div>
      </div>
    </div>
  );
}