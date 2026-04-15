import { BrowserRouter as Router, Routes, Route, Link, useLocation } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { QueryClient, QueryClientProvider, useQuery } from '@tanstack/react-query';
import api from './api';
import { ClientProvider, useClient } from './context/ClientContext';

const queryClient = new QueryClient();

import Clients from './pages/Clients';
import Pillars from './pages/Pillars';
import ContentPlans from './pages/ContentPlans';
import Drafts from './pages/Drafts';
import Calendar from './pages/Calendar';
import Settings from './pages/Settings';
import Insights from './pages/Insights';
import Dashboard from './pages/Dashboard';

import { 
  LayoutDashboard, 
  Users, 
  Target, 
  Zap, 
  FileText, 
  Calendar as CalendarIcon, 
  LineChart, 
  Settings as SettingsIcon,
  Search,
  CheckCircle2
} from 'lucide-react';

const SidebarItem = ({ to, label, icon: Icon }) => {
  const location = useLocation();
  const active = location.pathname === to || location.pathname.startsWith(to + '/');
  return (
    <Link to={to} className={`flex items-center gap-2.5 px-3 py-2.5 rounded-xl transition-colors ${active ? 'bg-blue-600 text-white shadow-md shadow-blue-100' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'}`}>
      <Icon size={18} strokeWidth={active ? 2 : 1.5} />
      <span className={`text-[13px] ${active ? 'font-semibold' : 'font-medium'}`}>{label}</span>
    </Link>
  );
};

const IdentityBadge = () => {
  const { selectedClient } = useClient();
  if (!selectedClient) return null;

  return (
    <div className="mx-3 mt-6 p-4 bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl border border-slate-700/50 shadow-2xl animate-in fade-in slide-in-from-left-4">
        <div className="flex items-center gap-3 mb-2">
            <div className="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-black text-xs">
                {selectedClient.name.charAt(0)}
            </div>
            <div className="flex-1 min-w-0">
                <div className="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-0.5 opacity-80">Selected Client</div>
                <div className="text-xs font-bold text-white truncate uppercase tracking-tight">{selectedClient.name}</div>
            </div>
        </div>
        <div className="space-y-1.5 mt-3 pt-3 border-t border-slate-700/50">
            <div className="flex items-center gap-2">
                <Target size={10} className="text-slate-500" />
                <span className="text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate">{selectedClient.niche}</span>
            </div>
            {selectedClient.location && (
                <div className="flex items-center gap-2">
                    <CheckCircle2 size={10} className="text-emerald-500/50" />
                    <span className="text-[9px] font-bold text-slate-500 uppercase tracking-widest truncate">{selectedClient.location}</span>
                </div>
            )}
        </div>
    </div>
  );
};

const ClientSelector = () => {
  const { selectedClientId, setSelectedClientId, setSelectedClient } = useClient();
  const { data: clients = [] } = useQuery({ 
      queryKey: ['clients'], 
      queryFn: () => api.get('/clients').then(res => res.data) 
  });

  const handleClientChange = (e) => {
    const id = e.target.value;
    setSelectedClientId(id);
    const client = clients.find(c => String(c.id) === id);
    setSelectedClient(client || null);
  };

  return (
    <div className="flex items-center gap-2 bg-slate-100 p-1 rounded-lg border border-slate-200">
      <div className="bg-white p-1.5 rounded-md shadow-sm border border-slate-200">
          <Search size={12} className="text-slate-400" />
      </div>
      <select 
        className="bg-transparent border-none text-slate-700 text-xs font-bold rounded-lg focus:ring-0 block w-full outline-none transition-all"
        value={selectedClientId}
        onChange={handleClientChange}
      >
        <option value="">All Managed Brands</option>
        {clients.map(client => (
          <option key={client.id} value={client.id}>{client.name}</option>
        ))}
      </select>
    </div>
  );
};

const Layout = ({ children }) => {
  return (
    <div className="flex h-screen bg-slate-50 text-slate-900 font-sans">
      {/* Sidebar */}
      <aside className="w-60 bg-slate-900 text-slate-200 flex flex-col shadow-xl flex-shrink-0">
        <div className="p-6 border-b border-slate-800 flex flex-col gap-1">
            <h1 className="text-xl font-black text-white uppercase tracking-[0.3em] bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">PRISM</h1>
            <p className="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Social Media Management</p>
        </div>

        {/* Global Identity Badge */}
        <IdentityBadge />
        
        <nav className="flex-1 p-3 space-y-1 overflow-y-auto">
          <div className="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 mt-4 px-3 font-mono">Workflow</div>
          <SidebarItem to="/" label="Dashboard" icon={LayoutDashboard} />
          <SidebarItem to="/clients" label="Clients" icon={Users} />
          <SidebarItem to="/pillars" label="Strategy Map" icon={Target} />
          <SidebarItem to="/plans" label="Ideation Roadmap" icon={Zap} />
          <SidebarItem to="/drafts" label="Draft Intelligence" icon={FileText} />
          <SidebarItem to="/calendar" label="Publisher Hub" icon={CalendarIcon} />
          
          <div className="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 mt-8 px-3 font-mono">System</div>
          <SidebarItem to="/insights" label="Insights" icon={LineChart} />
          <SidebarItem to="/settings" label="Settings" icon={SettingsIcon} />
        </nav>
      </aside>

      {/* Main Content */}
      <main className="flex-1 flex flex-col overflow-hidden">
        <header className="h-14 bg-white shadow-sm flex items-center px-6 flex-shrink-0 z-10 border-b border-slate-200">
             <div className="flex-1 max-w-[280px]">
                <ClientSelector />
             </div>
             <div className="ml-auto flex items-center gap-3">
                  <div className="h-7 w-7 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 border border-slate-300">A</div>
             </div>
        </header>
        <div className="flex-1 overflow-y-auto p-4 bg-slate-50">
          {children}
        </div>
      </main>
    </div>
  );
};

function App() {
  return (
    <QueryClientProvider client={queryClient}>
        <ClientProvider>
            <Router>
                <Toaster position="top-right" />
                <Layout>
                    <Routes>
                        <Route path="/" element={<Dashboard />} />
                        <Route path="/clients" element={<Clients />} />
                        <Route path="/pillars" element={<Pillars />} />
                        <Route path="/plans" element={<ContentPlans />} />
                        <Route path="/drafts/*" element={<Drafts />} />
                        <Route path="/calendar" element={<Calendar />} />
                        <Route path="/insights" element={<Insights />} />
                        <Route path="/settings" element={<Settings />} />
                    </Routes>
                </Layout>
            </Router>
        </ClientProvider>
    </QueryClientProvider>
  );
}

export default App;
