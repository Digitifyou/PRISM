import { useQuery } from '@tanstack/react-query';
import { Link } from 'react-router-dom';
import api from '../api';
import { useClient } from '../context/ClientContext';
import { 
  Building2, 
  FileText, 
  Rocket, 
  Search, 
  Library, 
  Lightbulb, 
  CheckCircle2,
  ArrowRight
} from 'lucide-react';

export default function Dashboard() {
  const { selectedClientId, selectedClient } = useClient();
  const { data: clients = [] } = useQuery({ queryKey: ['clients'], queryFn: () => api.get('/clients').then(res => res.data) });
  const { data: posts = [] } = useQuery({ 
    queryKey: ['posts', selectedClientId], 
    queryFn: () => api.get(`/posts?client_id=${selectedClientId}`).then(res => res.data) 
  });
  
  const drafts = posts.filter(p => p.status === 'draft');
  const published = posts.filter(p => p.status === 'published');

  return (
    <div className="max-w-7xl mx-auto space-y-8 py-6">
      <div className="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 p-8 rounded-2xl shadow-xl text-white relative overflow-hidden group">
        <div className="absolute top-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full -mr-32 -mt-32 blur-3xl transition-all duration-700"></div>
        <div className="relative z-10">
          <div className="flex items-center gap-3 mb-4">
             <div className="px-3 py-1 bg-blue-500/20 border border-blue-500/30 rounded-lg text-[10px] font-bold uppercase tracking-widest text-blue-400">
                Active Client
             </div>
             <div className="h-px w-12 bg-blue-500/20"></div>
          </div>
          
          <h1 className="text-4xl font-black mb-3 tracking-tight uppercase flex items-baseline gap-3">
             <span className="text-white">SOCIAL</span>
             <span className="text-blue-500">MEDIA</span>
             <span className="bg-gradient-to-r from-blue-100 to-slate-400 bg-clip-text text-transparent">
                DASHBOARD
             </span>
          </h1>

          <div className="flex flex-wrap gap-6 items-center mt-6 pt-6 border-t border-white/5">
             {selectedClient ? (
                <>
                  <div className="flex flex-col">
                      <span className="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1 opacity-60">Industry / Niche</span>
                      <span className="text-sm font-bold text-slate-200">{selectedClient.niche}</span>
                  </div>
                  {selectedClient.location && (
                    <div className="flex flex-col">
                        <span className="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1 opacity-60">Primary Location</span>
                        <span className="text-sm font-bold text-slate-200">{selectedClient.location}</span>
                    </div>
                  )}
                  {selectedClient.service_area && (
                    <div className="flex flex-col">
                        <span className="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1 opacity-60">Service Area</span>
                        <span className="text-sm font-bold text-slate-200">{selectedClient.service_area}</span>
                    </div>
                  )}
                </>
             ) : (
                <p className="text-blue-100/60 text-sm font-medium">Select a client to see relevant data.</p>
             )}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-slate-200 transition-all">
              <div>
                  <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Managed Brands</div>
                  <div className="text-2xl font-bold text-slate-900">{clients.length}</div>
              </div>
          </div>
          
          <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-slate-200 transition-all">
              <div>
                  <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Pending Drafts</div>
                  <div className="text-2xl font-bold text-slate-900">{drafts.length}</div>
              </div>
          </div>

          <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-slate-200 transition-all">
              <div>
                  <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Published Live</div>
                  <div className="text-2xl font-bold text-slate-900">{published.length}</div>
              </div>
              <div className="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform">
                  <CheckCircle2 size={24} strokeWidth={2} />
              </div>
          </div>
      </div>

      <div>
          <div className="flex items-center gap-4 mb-6">
              <h2 className="text-xl font-bold text-slate-900 tracking-tight">Workflow Quick Action</h2>
              <div className="h-px flex-1 bg-slate-100"></div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {[
                { to: '/clients', label: 'Extract Identity', desc: 'Scan websites for voice.', icon: Search, color: 'text-blue-600 bg-blue-50' },
                { to: '/pillars', label: 'Build Pillars', desc: 'Structural segments.', icon: Library, color: 'text-purple-600 bg-purple-50' },
                { to: '/plans', label: 'Run Ideation', desc: 'AI writers & designers.', icon: Lightbulb, color: 'text-amber-600 bg-amber-50' },
                { to: '/drafts', label: 'Review & Push', desc: 'Final audit & publish.', icon: Rocket, color: 'text-emerald-600 bg-emerald-50' }
              ].map((item, i) => (
                <Link key={i} to={item.to} className="group bg-white p-6 rounded-xl border border-slate-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col items-center text-center relative overflow-hidden">
                    <div className={`w-12 h-12 rounded-xl ${item.color} flex items-center justify-center mb-4 group-hover:scale-105 transition-all duration-500`}>
                        <item.icon size={24} strokeWidth={2} />
                    </div>
                    <h3 className="font-bold text-[14px] text-slate-900 mb-1">{item.label}</h3>
                    <p className="text-[12px] text-slate-500 font-medium leading-relaxed">{item.desc}</p>
                    <div className="mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <ArrowRight size={16} className="text-blue-600" />
                    </div>
                </Link>
              ))}
          </div>
      </div>
    </div>
  );
}
