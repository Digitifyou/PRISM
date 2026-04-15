import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '../api';
import { format } from 'date-fns';
import { useClient } from '../context/ClientContext';
import SocialIcon, { getBrandColor } from '../components/SocialIcon';
import { 
  Calendar as CalendarIcon, 
  Send, 
  CheckCircle2, 
  AlertCircle, 
  RefreshCw,
  FileText,
  Clock,
  ExternalLink
} from 'lucide-react';

export default function Calendar() {
  const { selectedClientId } = useClient();
  const queryClient = useQueryClient();
  const [filter, setFilter] = useState('approved');

  const { data: posts = [], isLoading } = useQuery({
    queryKey: ['posts', selectedClientId],
    queryFn: () => api.get(`/posts?client_id=${selectedClientId}`).then(res => res.data),
    refetchInterval: 10000
  });

  const displayPosts = posts.filter(p => filter === 'all' ? p.status !== 'draft' : p.status === filter);

  const publishMutation = useMutation({
    mutationFn: (id) => api.post(`/posts/${id}/publish`).then(res => res.data),
    onSuccess: () => {
      toast.success('Successfully Published to Social Channels');
      queryClient.invalidateQueries(['posts']);
    },
    onError: (err) => {
      toast.error('Publishing Failed: ' + (err.response?.data?.error || err.message));
    }
  });

  const getPlatformIcon = (plt) => {
      return <SocialIcon platform={plt} size={14} />;
  }

  return (
    <div className="max-w-7xl mx-auto space-y-8 py-4">
      <div className="flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-slate-100">
        <div className="flex items-center gap-4">
            <div>
                <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-indigo-600 bg-clip-text text-transparent uppercase tracking-[0.2em]">Post Calendar</h1>
                <p className="text-[10px] text-slate-500 font-black uppercase tracking-widest">Manage Scheduled Posts</p>
            </div>
        </div>
        <div className="flex gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100 shadow-inner">
            {['approved', 'published', 'failed', 'all'].map(f => (
                <button 
                  key={f}
                  onClick={() => setFilter(f)}
                  className={`px-4 py-2 text-[10px] font-bold uppercase tracking-widest transition-all ${filter === f ? 'bg-white shadow-md text-blue-600' : 'text-slate-400 hover:text-slate-600'}`}
                >
                    {f}
                </button>
            ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {isLoading ? (
               <div className="p-12 text-center col-span-full font-bold text-slate-300 tracking-[0.2em] uppercase">Fetching Posts...</div>
           ) : displayPosts.length === 0 ? (
               <div className="p-20 text-center col-span-full border-4 border-dashed border-slate-50 rounded-xl bg-white flex flex-col items-center">
                   <Clock size={48} className="text-slate-100 mb-4" />
                   <p className="text-slate-400 font-bold">No {filter} posts in the pipeline.</p>
                   <p className="text-slate-300 text-sm">Approve or Schedule drafts from the Review screen to populate this hub.</p>
               </div>
           ) : displayPosts.map(post => (
              <div key={post.id} className="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl hover:border-blue-100 transition-all flex flex-col group relative">
                  {post.image_url ? (
                     <div className="h-56 overflow-hidden bg-slate-100 relative">
                         <img src={`http://localhost:3001${post.image_url}`} alt="Thumbnail" className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                         <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                     </div>
                  ) : (
                     <div className="h-56 bg-slate-50 border-b border-slate-100 flex flex-col items-center justify-center text-slate-300 font-bold text-[10px] uppercase tracking-widest">
                         <FileText size={40} className="mb-3 opacity-20" />
                         Copy Only
                     </div>
                  )}
                  
                  <div className="p-5 flex-1 flex flex-col">
                      <div className="flex justify-between items-center mb-5">
                          <div 
                              className="px-3 py-1.5 rounded-lg font-bold text-[9px] uppercase tracking-widest flex items-center gap-2"
                              style={{ 
                                  backgroundColor: `${getBrandColor(post.platform)}10`, 
                                  color: getBrandColor(post.platform),
                                  border: `1px solid ${getBrandColor(post.platform)}20`
                              }}
                          >
                              {getPlatformIcon(post.platform)}
                              {post.platform}
                          </div>
                         
                          {post.status === 'published' && <span className="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-lg shadow-sm"><CheckCircle2 size={10} strokeWidth={3} /> Live</span>}
                          {post.status === 'failed' && <span className="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-rose-600 bg-rose-50 border border-rose-100 px-3 py-1.5 rounded-lg shadow-sm"><AlertCircle size={10} strokeWidth={3} /> Error</span>}
                          {post.status === 'approved' && (
                                <span className={`flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-sm border ${
                                    post.scheduled_at && new Date(post.scheduled_at) > new Date()
                                    ? 'text-indigo-600 bg-indigo-50 border-indigo-100'
                                    : 'text-blue-600 bg-blue-50 border-blue-100'
                                }`}>
                                    <Clock size={10} strokeWidth={3} /> 
                                    {post.scheduled_at && new Date(post.scheduled_at) > new Date() ? 'Scheduled' : 'Pending'}
                                </span>
                          )}
                      </div>
                      
                      <h3 className="text-sm font-bold text-slate-900 mb-2 tracking-tight group-hover:text-blue-600 transition-colors line-clamp-1">{post.topic}</h3>
                      <p className="text-sm text-slate-500 font-medium line-clamp-2 mb-6 leading-relaxed italic">"{post.caption}"</p>

                      {post.status === 'failed' && (
                          <div className="bg-rose-50/50 border border-rose-100 p-4 rounded-xl mb-6">
                              <p className="text-[10px] font-bold text-rose-800 uppercase tracking-widest flex items-center gap-2 mb-1">
                                  <AlertCircle size={12} /> Failure Report
                              </p>
                              <p className="text-xs text-rose-600 font-medium">{post.failure_reason}</p>
                          </div>
                      )}

                      <div className="pt-6 border-t border-slate-50 mt-auto flex flex-col gap-4">
                           {post.status === 'approved' && post.scheduled_at && (
                               <div className="flex items-center justify-between text-[10px] font-bold">
                                   <div className="text-slate-400 uppercase tracking-widest">Publish Window</div>
                                   <div className="text-blue-600 bg-blue-50 px-3 py-1 rounded flex items-center gap-2">
                                       <CalendarIcon size={12} />
                                       {format(new Date(post.scheduled_at), 'MMM d, ha')}
                                   </div>
                               </div>
                           )}

                           {post.status === 'approved' ? (
                               <button 
                                 onClick={() => publishMutation.mutate(post.id)}
                                 disabled={publishMutation.isPending}
                                 className={`w-full flex items-center justify-center gap-2 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-xl group/btn ${
                                    post.scheduled_at && new Date(post.scheduled_at) > new Date()
                                    ? 'bg-slate-50 text-slate-400 border border-slate-100 hover:bg-slate-100'
                                    : 'bg-slate-900 hover:bg-slate-800 text-white shadow-slate-200'
                                 }`}
                               >
                                 {publishMutation.isPending && publishMutation.variables === post.id ? (
                                      <RefreshCw size={16} className="animate-spin" />
                                 ) : post.scheduled_at && new Date(post.scheduled_at) > new Date() ? (
                                      <Clock size={16} />
                                 ) : (
                                      <Send size={16} className="group-hover/btn:translate-x-1 transition-transform" />
                                 )}
                                 {publishMutation.isPending && publishMutation.variables === post.id 
                                    ? 'Deploying...' 
                                    : post.scheduled_at && new Date(post.scheduled_at) > new Date()
                                        ? 'Scheduled'
                                        : 'Push to Live Channel'}
                               </button>
                           ) : post.status === 'published' ? (
                               <div className="w-full text-center py-4 bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 rounded-xl border border-slate-100 flex items-center justify-center gap-2">
                                 <ExternalLink size={12} /> 
                                 Broadcasted {format(new Date(post.published_at), 'MMM d, ha')}
                               </div>
                           ) : (
                               <button 
                                 onClick={() => publishMutation.mutate(post.id)}
                                 disabled={publishMutation.isPending}
                                 className="w-full flex items-center justify-center gap-2 py-4 bg-white border-2 border-slate-100 hover:border-blue-100 hover:text-blue-600 text-slate-400 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all"
                               >
                                 <RefreshCw size={16} className={publishMutation.isPending ? 'animate-spin' : ''} />
                                 Retry Deployment
                               </button>
                           )}
                       </div>
                  </div>
              </div>
          ))}
      </div>
    </div>
  );
}
