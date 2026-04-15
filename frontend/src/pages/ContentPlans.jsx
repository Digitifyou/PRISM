import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '../api';
import { format } from 'date-fns';
import { useClient } from '../context/ClientContext';
import SocialIcon, { getBrandColor } from '../components/SocialIcon';
import {
    Sparkles,
    Pencil,
    Trash2,
    Cpu,
    Smartphone,
    Clock,
    Plus,
    Zap,
    CheckCircle2,
    X,
    Lightbulb,
    Library
} from 'lucide-react';

export default function ContentPlans() {
    const { selectedClientId } = useClient();
    const queryClient = useQueryClient();
    const [showAdd, setShowAdd] = useState(false);
    const [editingPlan, setEditingPlan] = useState(null);
    const [activeGeneratingTopic, setActiveGeneratingTopic] = useState(null);

    const [formData, setFormData] = useState({
        client_id: selectedClientId,
        client_pillar_id: '',
        niche: '',
        frequency: 'weekly',
        ai_provider: 'gemini',
        platforms: ['linkedin']
    });

    const { data: clients = [] } = useQuery({ queryKey: ['clients'], queryFn: () => api.get('/clients').then(res => res.data) });
    const { data: pillars = [] } = useQuery({
        queryKey: ['pillars', selectedClientId],
        queryFn: () => api.get(`/pillars?client_id=${selectedClientId}`).then(res => res.data)
    });

    const { data: plans = [], isLoading } = useQuery({
        queryKey: ['plans', selectedClientId],
        queryFn: () => api.get(`/plans?client_id=${selectedClientId}`).then(res => res.data),
        refetchInterval: 5000
    });

    const saveMutation = useMutation({
        mutationFn: (data) => data.id
            ? api.put(`/plans/${data.id}`, data).then(res => res.data)
            : api.post('/plans', data).then(res => res.data),
        onSuccess: () => {
            toast.success(editingPlan ? 'Plan Updated' : 'Strategy Engine Started!');
            setShowAdd(false);
            setEditingPlan(null);
            setFormData({ client_id: selectedClientId, client_pillar_id: '', niche: '', frequency: 'weekly', ai_provider: 'gemini', platforms: ['linkedin'] });
            queryClient.invalidateQueries(['plans']);
        },
        onError: (err) => toast.error('Failed: ' + (err.response?.data?.error || err.message))
    });

    const deleteMutation = useMutation({
        mutationFn: (id) => api.delete(`/plans/${id}`).then(res => res.data),
        onSuccess: () => {
            toast.success('Plan Deleted');
            queryClient.invalidateQueries(['plans']);
        }
    });

    const generateTopicMutation = useMutation({
        mutationFn: ({ planId, topic }) => api.post(`/plans/${planId}/generate-topic`, { topic }),
        onSuccess: () => {
            toast.success('Topic generated! Check "Review" page for the draft.');
            setActiveGeneratingTopic(null);
        },
        onError: (err) => {
            toast.error('Generation failed: ' + (err.response?.data?.error || err.message));
            setActiveGeneratingTopic(null);
        }
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        saveMutation.mutate(formData);
    };

    const handleGenerateClick = (planId, topic) => {
        setActiveGeneratingTopic(`${planId}-${topic}`);
        generateTopicMutation.mutate({ planId, topic });
    };

    const startEdit = (plan) => {
        setEditingPlan(plan);
        setFormData({
            id: plan.id,
            client_id: plan.client_id,
            client_pillar_id: plan.client_pillar_id,
            niche: plan.niche,
            frequency: plan.frequency,
            ai_provider: plan.ai_provider,
            platforms: Array.isArray(plan.platforms) ? plan.platforms : JSON.parse(plan.platforms || '[]')
        });
        setShowAdd(true);
    };

    const handleDelete = (id) => {
        if (window.confirm('Delete this strategic plan? Your generated drafts and headlines WILL BE PRESERVED safely.')) {
            deleteMutation.mutate(id);
        }
    };

    const togglePlatform = (platform) => {
        setFormData(prev => ({
            ...prev,
            platforms: prev.platforms.includes(platform)
                ? prev.platforms.filter(p => p !== platform)
                : [...prev.platforms, platform]
        }));
    };

    const getPlatformIcon = (plt) => {
        if (['facebook', 'instagram', 'linkedin', 'google_business'].includes(plt)) {
            return <SocialIcon platform={plt} size={14} />;
        }
        return <Smartphone size={14} />;
    }

    return (
        <div className="max-w-7xl mx-auto space-y-6">
            <div className="flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div className="flex items-center gap-4">
                    <div>
                        <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent uppercase tracking-[0.2em]">Ideation Roadmap</h1>
                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Content Research & Planning Engine</p>
                    </div>
                </div>
                <button
                    onClick={() => { setShowAdd(!showAdd); setEditingPlan(null); }}
                    className={`px-4 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 ${showAdd ? 'bg-slate-100 text-slate-600' : 'bg-blue-600 text-white shadow-xl shadow-blue-100'}`}
                >
                    {showAdd ? <X size={20} /> : <Plus size={20} />}
                    {showAdd ? 'Cancel' : 'New Strategy Plan'}
                </button>
            </div>

            {showAdd && (
                <form onSubmit={handleSubmit} className="bg-white p-10 rounded-xl shadow-2xl border border-blue-100 space-y-8 animate-in slide-in-from-top-4">
                    <div className="flex items-center gap-4 border-b border-slate-100 pb-6">
                        <div className="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <Zap size={20} />
                        </div>
                        <h2 className="text-2xl font-bold text-slate-800">
                            {editingPlan ? `Update Strategy: ${editingPlan.niche}` : 'Initialize AI Pipeline'}
                        </h2>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div className="space-y-2">
                            <label className="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">Brand Identity</label>
                            <select required className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold" value={formData.client_id} onChange={e => setFormData({ ...formData, client_id: e.target.value })}>
                                <option value="">Select a Client...</option>
                                {clients.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">Strategy Anchor</label>
                            <select required disabled={!formData.client_id} className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none disabled:opacity-50 font-bold" value={formData.client_pillar_id} onChange={e => setFormData({ ...formData, client_pillar_id: e.target.value })}>
                                <option value="">Select Pillar Context...</option>
                                {pillars.map(p => <option key={p.id} value={p.id}>{p.title}</option>)}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">Specific Niche Focus</label>
                            <input required type="text" placeholder="e.g. Serverless Architecture for start-ups" className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold" value={formData.niche} onChange={e => setFormData({ ...formData, niche: e.target.value })} />
                        </div>
                        <div className="space-y-2">
                            <label className="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">AI Intelligence Layer</label>
                            <div className="relative">
                                <Cpu className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                                <select className="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold appearance-none" value={formData.ai_provider} onChange={e => setFormData({ ...formData, ai_provider: e.target.value })}>
                                    <option value="gemini">Google Gemini 2.0</option>
                                    <option value="openai">OpenAI GPT-4o</option>
                                    <option value="claude">Anthropic Claude 3.5</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-4 pt-4">
                        <label className="text-xs font-bold text-slate-400 uppercase tracking-widest px-1 flex items-center gap-2">
                            <Smartphone size={14} /> Propagation Channels
                        </label>
                        <div className="flex flex-wrap gap-4">
                            {['facebook', 'instagram', 'linkedin', 'google_business'].map(platform => (
                                <label key={platform} className={`flex items-center gap-3 px-6 py-4 rounded-xl cursor-pointer border-2 transition-all ${formData.platforms.includes(platform) ? 'bg-blue-50 border-blue-200 shadow-md transform -translate-y-0.5' : 'bg-white border-slate-100 hover:border-slate-200 text-slate-400'}`}>
                                    <input type="checkbox" className="hidden" checked={formData.platforms.includes(platform)} onChange={() => togglePlatform(platform)} />
                                    <div className={`w-6 h-6 rounded-lg flex items-center justify-center ${formData.platforms.includes(platform) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'}`}>
                                        {getPlatformIcon(platform)}
                                    </div>
                                    <span className="font-bold capitalize">{platform}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 pt-8 border-t border-slate-50">
                        <button type="button" onClick={() => { setShowAdd(false); setEditingPlan(null); }} className="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition-colors">Discard</button>
                        <button type="submit" disabled={saveMutation.isPending || formData.platforms.length === 0} className="px-12 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition-all flex items-center gap-3 shadow-2xl shadow-slate-200">
                            {saveMutation.isPending ? (
                                <div className="w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                            ) : <Zap size={20} className="fill-current text-amber-400" />}
                            {saveMutation.isPending ? 'Processing...' : editingPlan ? 'Save Changes' : 'Execute AI Strategy'}
                        </button>
                    </div>
                </form>
            )}

            <div className="space-y-6">
                {isLoading ? (
                    <div className="p-6 text-center text-slate-400 font-bold tracking-widest uppercase">Initializing History Hub...</div>
                ) : plans.length === 0 ? (
                    <div className="p-20 text-center border-4 border-dashed border-slate-100 rounded-[3rem] bg-white flex flex-col items-center">
                        <Zap size={48} className="text-slate-200 mb-4" />
                        <p className="text-slate-400 font-bold">No strategies executed yet. Pull the trigger on a new plan above.</p>
                    </div>
                ) : plans.map(plan => {
                    let topicList = [];
                    try {
                        topicList = Array.isArray(plan.topics) ? plan.topics : JSON.parse(plan.topics || '[]');
                    } catch (e) { topicList = []; }

                    const generatedTopicNames = (plan.posts || []).map(p => p.topic);
                    const filteredTopics = topicList.filter(t => !generatedTopicNames.includes(t));
                    const platforms = Array.isArray(plan.platforms) ? plan.platforms : JSON.parse(plan.platforms || '[]');

                    return (
                        <div key={plan.id} className="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden group relative hover:shadow-xl transition-all">
                            <div className="p-5 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                                <div className="pr-24">
                                    <div className="flex items-center gap-3 mb-3">
                                        <span className="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-[10px] font-bold uppercase tracking-widest">{plan.Client?.name}</span>
                                        <span className="px-3 py-1 bg-white text-slate-500 rounded-lg text-[10px] font-bold uppercase tracking-widest border border-slate-200 flex items-center gap-2">
                                            <Library size={10} /> {plan.pillar?.title}
                                        </span>
                                    </div>
                                    <h3 className="font-bold text-2xl text-slate-900 mb-3 tracking-tight">{plan.niche}</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-[10px] font-bold uppercase tracking-widest">
                                        <span className="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100 w-max" style={{ color: getBrandColor(plan.ai_provider) }}>
                                            <SocialIcon platform={plan.ai_provider} size={14} /> 
                                            {plan.ai_provider}
                                        </span>
                                        <span className="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100 w-max" style={{ color: getBrandColor(platforms[0]) }}>
                                            <SocialIcon platform={platforms[0]} size={14} /> 
                                            {platforms.join(' • ')}
                                        </span>
                                        <span className="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100 w-max text-slate-400">
                                            <Clock size={12} className="text-emerald-400" /> 
                                            {format(new Date(plan.created_at || plan.createdAt), 'MMM d, h:mm a')}
                                        </span>
                                    </div>
                                </div>

                                <div className="flex flex-col items-end gap-3">
                                    <div className="flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <button onClick={() => startEdit(plan)} className="p-3 bg-white hover:bg-slate-900 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Edit Plan"><Pencil size={18} /></button>
                                        <button onClick={() => handleDelete(plan.id)} className="p-3 bg-white hover:bg-rose-600 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Delete Plan"><Trash2 size={18} /></button>
                                    </div>

                                    {plan.status === 'completed' ? (
                                        <div className="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 font-bold text-[10px] uppercase tracking-widest">
                                            <CheckCircle2 size={14} />
                                            {filteredTopics.length > 0 ? 'Titles Mapped' : 'Drafts Secured'}
                                        </div>
                                    ) : plan.status === 'failed' ? (
                                        <div className="flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-700 rounded-xl border border-rose-100 font-bold text-[10px] uppercase tracking-widest">
                                            <X size={14} /> Error
                                        </div>
                                    ) : (
                                        <div className="flex items-center gap-3 px-4 py-2 bg-blue-50 text-blue-700 rounded-xl border border-blue-100 font-bold text-[10px] uppercase tracking-widest">
                                            <div className="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></div>
                                            Extracting Ideas...
                                        </div>
                                    )}
                                </div>
                            </div>

                            {filteredTopics.length > 0 && (
                                <div className="p-5">
                                    <div className="flex items-center gap-3 mb-6">
                                        <h4 className="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Strategy Title Map</h4>
                                        <div className="h-px flex-1 bg-slate-50"></div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {filteredTopics.map((topic, idx) => {
                                            const isGenerating = activeGeneratingTopic === `${plan.id}-${topic}`;
                                            return (
                                                <div key={idx} className="flex items-center justify-between p-5 bg-slate-50/50 rounded-xl border border-slate-100 group/item hover:border-blue-200 hover:bg-white transition-all">
                                                    <span className="text-sm font-bold text-slate-700 pr-4 leading-tight">{topic}</span>
                                                    <button
                                                        disabled={isGenerating}
                                                        onClick={() => handleGenerateClick(plan.id, topic)}
                                                        className={`flex-shrink-0 px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest transition-all ${isGenerating
                                                                ? 'bg-blue-100 text-blue-300 cursor-not-allowed'
                                                                : 'bg-white border border-slate-200 text-blue-600 hover:bg-slate-900 hover:text-white hover:border-slate-900 shadow-sm'
                                                            }`}
                                                    >
                                                        {isGenerating ? (
                                                            <div className="flex items-center gap-2">
                                                                <div className="w-3 h-3 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                                                                Writing...
                                                            </div>
                                                        ) : (
                                                            <div className="flex items-center gap-2">
                                                                <Sparkles size={12} className="group-hover/item:animate-bounce" /> Draft
                                                            </div>
                                                        )}
                                                    </button>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
