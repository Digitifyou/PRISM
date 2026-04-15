import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '../api';
import { useClient } from '../context/ClientContext';
import {
  Plus,
  Sparkles,
  Pencil,
  Trash2,
  Library,
  ChevronRight
} from 'lucide-react';

export default function Pillars() {
  const { selectedClientId } = useClient();
  const queryClient = useQueryClient();
  const [showAdd, setShowAdd] = useState(false);
  const [editingPillar, setEditingPillar] = useState(null);
  const [formData, setFormData] = useState({ client_id: selectedClientId, title: '', description: '' });

  const { data: clients = [] } = useQuery({ queryKey: ['clients'], queryFn: () => api.get('/clients').then(res => res.data) });
  const { data: pillars = [], isLoading } = useQuery({
    queryKey: ['pillars', selectedClientId],
    queryFn: () => api.get(`/pillars?client_id=${selectedClientId}`).then(res => res.data)
  });

  const saveMutation = useMutation({
    mutationFn: (data) => data.id
      ? api.put(`/pillars/${data.id}`, data).then(res => res.data)
      : api.post('/pillars', data).then(res => res.data),
    onSuccess: () => {
      toast.success(editingPillar ? 'Pillar Updated' : 'Pillar Added');
      setShowAdd(false);
      setEditingPillar(null);
      setFormData({ client_id: selectedClientId, title: '', description: '' });
      queryClient.invalidateQueries(['pillars']);
    },
    onError: (err) => toast.error('Save failed: ' + (err.response?.data?.error || err.message))
  });

  const deleteMutation = useMutation({
    mutationFn: (id) => api.delete(`/pillars/${id}`).then(res => res.data),
    onSuccess: () => {
      toast.success('Pillar Deleted');
      queryClient.invalidateQueries(['pillars']);
    }
  });

  const generateMutation = useMutation({
    mutationFn: (clientId) => api.post('/pillars/generate', { client_id: clientId }).then(res => res.data),
    onSuccess: () => {
      toast.success('Pillars Generated & Saved Successfully', { id: 'generate' });
      queryClient.invalidateQueries(['pillars']);
    },
    onError: (err) => {
      toast.error('Generation failed: ' + (err.response?.data?.error || err.message));
    }
  });

  const handleGenerate = () => {
    if (!selectedClientId) {
      toast.error('Please select a client first');
      return;
    }
    toast.loading('AI is crafting Strategy Pillars...', { id: 'generate' });
    generateMutation.mutate(selectedClientId, {
      onError: () => toast.dismiss('generate')
    });
  };

  const startEdit = (pillar) => {
    setEditingPillar(pillar);
    setFormData({ id: pillar.id, client_id: pillar.client_id, title: pillar.title, description: pillar.description });
    setShowAdd(true);
  };

  const handleDelete = (id) => {
    if (window.confirm('Are you sure you want to delete this pillar? This will affect existing content plans.')) {
      deleteMutation.mutate(id);
    }
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-5 rounded-xl shadow-sm border border-slate-100">
        <div className="flex items-center gap-4">
          <div>
            <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent uppercase tracking-[0.2em]">Strategy Map</h1>
            <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Global Brand Context & Knowledge Base</p>
          </div>
        </div>
        <div className="flex gap-4">
          <button
            onClick={() => { setShowAdd(true); setEditingPillar(null); setFormData({ client_id: selectedClientId, title: '', description: '' }); }}
            className="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 transition-all flex items-center gap-2"
          >
            <Plus size={18} /> Add Manually
          </button>
          <button
            onClick={handleGenerate}
            disabled={generateMutation.isPending || !selectedClientId}
            className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition-all shadow-xl shadow-slate-200 disabled:opacity-50 flex items-center gap-2"
          >
            {generateMutation.isPending ? (
              <div className="w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
            ) : <Sparkles size={18} />}
            Generate with AI
          </button>
        </div>
      </div>

      {showAdd && (
        <div className="bg-white p-5 rounded-xl shadow-2xl border border-blue-100 animate-in slide-in-from-top-4">
          <h2 className="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
              {editingPillar ? <Pencil size={16} /> : <Plus size={16} />}
            </div>
            {editingPillar ? 'Edit Pillar' : 'Add New Pillar'}
          </h2>
          <form onSubmit={(e) => { e.preventDefault(); saveMutation.mutate(formData); }} className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-2">
                <label className="text-sm font-bold text-slate-700 uppercase tracking-widest px-1">Client</label>
                <select required className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-medium" value={formData.client_id} onChange={e => setFormData({ ...formData, client_id: e.target.value })}>
                  <option value="">Select Client</option>
                  {clients.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
              </div>
              <div className="space-y-2">
                <label className="text-sm font-bold text-slate-700 uppercase tracking-widest px-1">Pillar Title</label>
                <input required type="text" className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-medium" value={formData.title} onChange={e => setFormData({ ...formData, title: e.target.value })} placeholder="e.g. Technical SEO Insights" />
              </div>
            </div>
            <div className="space-y-2">
              <label className="text-sm font-bold text-slate-700 uppercase tracking-widest px-1">Description / Goal</label>
              <textarea required rows="4" className="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-medium leading-relaxed" value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} placeholder="What kind of content should this pillar focus on?" />
            </div>
            <div className="flex justify-end gap-3 pt-6 border-t border-slate-100">
              <button type="button" onClick={() => setShowAdd(false)} className="px-5 py-2.5 text-xs bg-slate-100 text-slate-600 rounded-xl font-bold">Cancel</button>
              <button type="submit" disabled={saveMutation.isPending} className="px-6 py-2.5 text-xs bg-blue-600 text-white rounded-xl font-bold shadow-xl shadow-blue-100">
                {saveMutation.isPending ? 'Saving...' : 'Save Strategy Anchor'}
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {isLoading ? (
          <div className="p-12 text-center col-span-full font-bold text-slate-400">Loading pillars...</div>
        ) : pillars.length === 0 ? (
          <div className="p-20 text-center col-span-full border-4 border-dashed border-slate-100 rounded-[3rem] bg-white text-slate-400 flex flex-col items-center">
            <Library size={64} className="mb-6 opacity-20" />
            <p className="text-xl font-bold">No pillars defined yet.</p>
            <p className="font-medium">Select a client and generate them to start building the foundation.</p>
          </div>
        ) : (
          pillars.map(pillar => (
            <div key={pillar.id} className="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:border-blue-200 hover:shadow-xl transition-all group relative">
               <div className="flex justify-between items-start mb-6">
                 <div>
                   <h3 className="font-bold text-xl text-slate-900 group-hover:text-blue-600 transition-colors pr-16 mb-1">{pillar.title}</h3>
                   {!selectedClientId && (
                     <span className="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-[0.15em]">
                       {pillar.Client?.name || 'Unknown'}
                     </span>
                   )}
                 </div>
                 <div className="flex gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <button onClick={() => startEdit(pillar)} className="p-3 bg-white hover:bg-slate-900 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Edit Pillar">
                        <Pencil size={18} />
                    </button>
                    <button onClick={() => handleDelete(pillar.id)} className="p-3 bg-white hover:bg-rose-600 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Delete Pillar">
                        <Trash2 size={18} />
                    </button>
                 </div>
               </div>
                
              <div className="bg-slate-50/50 p-6 rounded-3xl border border-slate-100 group-hover:bg-white transition-colors">
                <p className="text-slate-600 leading-relaxed ">
                  "{pillar.description}"
                </p>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
