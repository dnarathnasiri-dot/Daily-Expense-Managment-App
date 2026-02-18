import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
  Squares2X2Icon,
  PlusCircleIcon,
  ListBulletIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/react/24/outline';

const navItems = [
  { to: '/dashboard',    label: 'Dashboard',    Icon: Squares2X2Icon },
  { to: '/add-expense',  label: 'Add Expense',  Icon: PlusCircleIcon },
  { to: '/expense-list', label: 'Expense List', Icon: ListBulletIcon },
  { to: '/reports',      label: 'Reports',      Icon: ChartBarIcon },
  { to: '/settings',     label: 'Settings',     Icon: Cog6ToothIcon },
];

export default function Sidebar() {
  const { logout } = useAuth();
  const navigate   = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <aside className="fixed top-0 left-0 h-full w-64 bg-white border-r border-gray-200 flex flex-col z-10">
      {/* Logo */}
      <div className="p-6 border-b border-gray-100">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
            <span className="text-white text-lg font-bold">💳</span>
          </div>
          <div>
            <p className="font-bold text-gray-900 leading-tight">ExpenseTracker</p>
            <p className="text-xs text-gray-500">Manage your expenses</p>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 p-4 space-y-1">
        {navItems.map(({ to, label, Icon }) => (
          <NavLink
            key={to}
            to={to}
            className={({ isActive }) =>
              `flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                isActive
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
              }`
            }
          >
            <Icon className="w-5 h-5" />
            {label}
          </NavLink>
        ))}
      </nav>

      {/* Logout */}
      <div className="p-4 border-t border-gray-100">
        <button
          onClick={handleLogout}
          className="flex items-center gap-3 px-4 py-2.5 w-full rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors"
        >
          <ArrowRightOnRectangleIcon className="w-5 h-5" />
          Log Out
        </button>
        <p className="text-xs text-gray-400 mt-3 text-center">© 2026 ExpenseTracker</p>
      </div>
    </aside>
  );
}