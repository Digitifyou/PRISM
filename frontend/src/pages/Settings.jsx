import { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '../api';
import FBConnectButton from '../components/FBConnectButton';
import { Share2, Lock, Layout, Globe, CheckCircle2, AlertCircle } from 'lucide-react';

export default function Settings() {
  const queryClient = useQueryClient();
  const [formData, setFormData] = useState({
      openai_api_key: '',
      gemini_api_key: '',
      anthropic_api_key: '',
      tavily_api_key: '',
      facebook_access_token: '',
      facebook_page_id: '',
      instagram_access_token: '',
      instagram_account_id: '',
      linkedin_access_token: '',
      linkedin_person_id: '',
      google_business_token: '',
      google_business_location_id: ''
  });

  const { data: settings = [], isLoading } = useQuery({
    queryKey: ['settings'],
    queryFn: () => api.get('/settings').then(res => res.data)
  });

  useEffect(() => {
    if (settings.length > 0) {
        const obj = { ...formData };
        settings.forEach(s => {
            if (s.key in obj) obj[s.key] = s.value;
        });
        setFormData(obj);
    }
  }, [settings]);

  const saveMutation = useMutation({
    mutationFn: (data) => api.post('/settings', { settings: data }).then(res => res.data),
    onSuccess: () => {
      toast.success('Settings Saved Successfully');
      queryClient.invalidateQueries(['settings']);
    },
    onError: (err) => toast.error('Failed to save settings: ' + err.message)
  });

  const handleSubmit = (e) => {
      e.preventDefault();
      saveMutation.mutate(formData);
  };

  const handleChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

  if (isLoading) return <div className="p-5 text-center text-slate-500">Loading settings...</div>;

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <div>
          <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">System Configurations</h1>
          <p className="text-slate-500 mt-1">Connect your AI engines and Social Media profiles</p>
        </div>
        <button 
           onClick={handleSubmit} 
           disabled={saveMutation.isPending}
           className="px-4 py-2 text-xs font-bold uppercase tracking-widest transition-all bg-blue-600 text-white rounded-xl shadow-md hover:bg-blue-700"
        >
            {saveMutation.isPending ? 'Saving...' : 'Save Changes'}
        </button>
      </div>

      <form className="space-y-6">
          <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 space-y-6">
              <h2 className="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">🧠 Artificial Intelligence Models</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">OpenAI API Key</label>
                    <input type="password" name="openai_api_key" value={formData.openai_api_key} onChange={handleChange} className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="sk-..." />
                 </div>
                 <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">Google Gemini API Key</label>
                    <input type="password" name="gemini_api_key" value={formData.gemini_api_key} onChange={handleChange} className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="AIza..." />
                 </div>
                 <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">Anthropic (Claude) API Key</label>
                    <input type="password" name="anthropic_api_key" value={formData.anthropic_api_key} onChange={handleChange} className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="sk-ant-..." />
                 </div>
                 <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">Tavily Search API Key (Research)</label>
                    <input type="password" name="tavily_api_key" value={formData.tavily_api_key} onChange={handleChange} className="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="tvly-..." />
                 </div>
              </div>
          </div>

          <div className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 space-y-6">
              <h2 className="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">📱 Social Media Integrations</h2>
              
              <div className="space-y-6">
                  <div className="p-5 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl shadow-sm space-y-6">
                      <div className="flex justify-between items-start">
                          <div>
                              <h3 className="font-bold text-blue-900 text-lg">Meta Social Hub</h3>
                              <p className="text-xs text-blue-600 font-medium">Connect your Facebook Pages & Instagram Business Accounts</p>
                          </div>
                          {(formData.facebook_access_token || formData.instagram_access_token) ? (
                              <span className="flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                  <CheckCircle2 size={12} /> Connected
                              </span>
                          ) : (
                              <span className="flex items-center gap-1.5 px-3 py-1 bg-slate-200 text-slate-600 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                  Not Linked
                              </span>
                          )}
                      </div>

                      <div className="flex flex-col gap-4">
                          <FBConnectButton onComplete={() => queryClient.invalidateQueries(['settings'])} />
                          
                          {(formData.facebook_page_id || formData.instagram_account_id) && (
                              <div className="grid grid-cols-2 gap-4 mt-2 p-3 bg-white/50 rounded-xl border border-blue-100/50">
                                  <div>
                                      <label className="block text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Active Page ID</label>
                                      <div className="text-sm font-mono text-slate-600">{formData.facebook_page_id || 'None'}</div>
                                  </div>
                                  <div>
                                      <label className="block text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">IG Business ID</label>
                                      <div className="text-sm font-mono text-slate-600">{formData.instagram_account_id || 'None'}</div>
                                  </div>
                              </div>
                          )}
                      </div>

                      <div className="text-[10px] text-blue-400/80 leading-relaxed italic">
                          Prism secures Long-Lived (60-day) tokens to ensure your scheduled posts publish even when you are offline.
                      </div>
                  </div>

                  <div className="p-4 bg-sky-50 border border-sky-100 rounded-xl space-y-4">
                      <h3 className="font-bold text-sky-800">LinkedIn Profile</h3>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <label className="block text-sm font-semibold text-sky-900 mb-2">Person URN ID</label>
                            <input type="text" name="linkedin_person_id" value={formData.linkedin_person_id} onChange={handleChange} className="w-full px-4 py-2 border border-sky-200 rounded-lg outline-none" />
                          </div>
                          <div>
                            <label className="block text-sm font-semibold text-sky-900 mb-2">Access Token</label>
                            <input type="password" name="linkedin_access_token" value={formData.linkedin_access_token} onChange={handleChange} className="w-full px-4 py-2 border border-sky-200 rounded-lg outline-none" />
                          </div>
                      </div>
                  </div>

                  <div className="p-4 bg-blue-50/50 border border-blue-100 rounded-xl space-y-4">
                      <h3 className="font-bold text-blue-800">Google Business Profile (GMB)</h3>
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div>
                            <label className="block text-sm font-semibold text-blue-900 mb-2">Location ID</label>
                            <input type="text" name="google_business_location_id" value={formData.google_business_location_id} onChange={handleChange} className="w-full px-4 py-2 border border-blue-200 rounded-lg outline-none" placeholder="locations/1234..." />
                          </div>
                          <div>
                            <label className="block text-sm font-semibold text-blue-900 mb-2">Access Token</label>
                            <input type="password" name="google_business_token" value={formData.google_business_token} onChange={handleChange} className="w-full px-4 py-2 border border-blue-200 rounded-lg outline-none" />
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </form>
    </div>
  );
}
