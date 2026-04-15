import { useState, useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import api from '../api';
import toast from 'react-hot-toast';

export default function Insights() {
  const { data: posts = [], isLoading } = useQuery({
    queryKey: ['posts'],
    queryFn: () => api.get('/posts').then(res => res.data)
  });

  const publishedPosts = posts.filter(p => p.status === 'published');
  
  // We'll store insights dynamically as we fetch them per post
  const [insightsMap, setInsightsMap] = useState({});
  const [loadingMap, setLoadingMap] = useState({});

  const fetchInsights = async (post) => {
      setLoadingMap(prev => ({...prev, [post.id]: true}));
      try {
          const res = await api.get(`/posts/${post.id}/insights`);
          setInsightsMap(prev => ({...prev, [post.id]: res.data}));
      } catch (e) {
          toast.error(`Failed to fetch insights for ${post.topic}: ${e.response?.data?.error || e.message}`);
      } finally {
          setLoadingMap(prev => ({...prev, [post.id]: false}));
      }
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <div>
          <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">Performance Insights</h1>
          <p className="text-slate-500 mt-1">Live metrics directly from the social APIs</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {isLoading ? (
               <div className="p-8 text-center col-span-full font-medium text-slate-500">Loading published content...</div>
           ) : publishedPosts.length === 0 ? (
               <div className="p-6 text-center col-span-full border-2 border-dashed border-slate-200 rounded-xl bg-white text-slate-500">
                   You haven't published any posts yet. Publish content from the Calendar to see insights here.
               </div>
           ) : publishedPosts.map(post => (
              <div key={post.id} className="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex flex-col">
                  <div className="flex justify-between items-start mb-4">
                     <span className={`text-xs font-bold uppercase tracking-wider px-2 py-1 rounded ${
                         post.platform === 'facebook' ? 'bg-blue-100 text-blue-700' :
                         post.platform === 'instagram' ? 'bg-pink-100 text-pink-700' :
                         post.platform === 'google_business' ? 'bg-blue-50 text-blue-600 border border-blue-100' :
                         'bg-sky-100 text-sky-700'
                     }`}>
                        {post.platform === 'google_business' ? 'Google Business' : post.platform}
                     </span>
                     <button 
                        onClick={() => fetchInsights(post)}
                        disabled={loadingMap[post.id]}
                        className="text-xs font-semibold px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50"
                     >
                         {loadingMap[post.id] ? 'Syncing...' : '🔄 Sync Stats'}
                     </button>
                  </div>
                  
                  <h3 className="text-sm font-bold text-slate-800 mb-4">{post.topic}</h3>
                  
                  {insightsMap[post.id] ? (
                      <div className="grid grid-cols-2 gap-3 mt-auto">
                          <div className="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                              <div className="text-2xl font-bold text-slate-900">{insightsMap[post.id].impressions}</div>
                              <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Impressions</div>
                          </div>
                          <div className="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                              <div className="text-2xl font-bold text-slate-900">{insightsMap[post.id].reach}</div>
                              <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Reach</div>
                          </div>
                          <div className="bg-emerald-50 p-3 rounded-xl border border-emerald-100 text-center">
                              <div className="text-2xl font-bold text-emerald-700">{insightsMap[post.id].engagement_rate}%</div>
                              <div className="text-xs font-semibold text-emerald-600 uppercase tracking-wider mt-1">Eng. Rate</div>
                          </div>
                          <div className="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center justify-center gap-4">
                              <div className="text-center">
                                  <div className="font-bold text-slate-700">{insightsMap[post.id].likes}</div>
                                  <div className="text-[10px] uppercase text-slate-400">♥</div>
                              </div>
                              <div className="text-center">
                                  <div className="font-bold text-slate-700">{insightsMap[post.id].comments}</div>
                                  <div className="text-[10px] uppercase text-slate-400">💬</div>
                              </div>
                          </div>
                      </div>
                  ) : (
                      <div className="mt-auto py-8 text-center text-sm font-medium text-slate-400 border border-slate-100 rounded-xl bg-slate-50/50">
                          Click Sync Stats to pull live metrics
                      </div>
                  )}
              </div>
          ))}
      </div>
    </div>
  );
}
