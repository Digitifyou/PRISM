import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import toast from 'react-hot-toast';
import api from '../api';
import { Pencil, Trash2 } from 'lucide-react';

export default function Clients() {
  const queryClient = useQueryClient();
  const [showAdd, setShowAdd] = useState(false);
  const [url, setUrl] = useState('');
  const [isManual, setIsManual] = useState(false);
  const [manualFormData, setManualFormData] = useState({});
  
  const [editingClient, setEditingClient] = useState(null);
  const [editFormData, setEditFormData] = useState({});

  const { data: clients = [], isLoading } = useQuery({
    queryKey: ['clients'],
    queryFn: () => api.get('/clients').then(res => res.data)
  });

  const discoverMutation = useMutation({
    mutationFn: (websiteUrl) => api.post('/clients/discover', { website_url: websiteUrl }).then(res => res.data),
    onSuccess: (data) => {
        saveClientMutation.mutate(data);
    },
    onError: (err) => {
      toast.error('Discovery failed: ' + (err.response?.data?.error || err.message));
    }
  });

  const saveClientMutation = useMutation({
    mutationFn: (clientData) => api.post('/clients', clientData).then(res => res.data),
    onSuccess: () => {
      toast.success('Client Added Successfully');
      setShowAdd(false);
      setUrl('');
      queryClient.invalidateQueries(['clients']);
    }
  });

  const deleteClientMutation = useMutation({
    mutationFn: (id) => api.delete(`/clients/${id}`).then(res => res.data),
    onSuccess: () => {
      toast.success('Client Deleted');
      queryClient.invalidateQueries(['clients']);
    }
  });

  const updateClientMutation = useMutation({
    mutationFn: ({ id, ...data }) => api.put(`/clients/${id}`, data).then(res => res.data),
    onSuccess: () => {
      toast.success('Client Updated');
      setEditingClient(null);
      queryClient.invalidateQueries(['clients']);
    }
  });

  const handleDiscover = (e) => {
      e.preventDefault();
      if(!url) return;
      toast.loading('Analyzing Website via AI...', { id: 'discovery' });
      discoverMutation.mutate(url, {
          onSuccess: () => toast.success('Strategy extracted!', { id: 'discovery' }),
          onError: () => toast.dismiss('discovery')
      });
  };

  const startEditing = (client) => {
    setEditingClient(client);
    setEditFormData(client);
  };

  const handleEditChange = (e) => {
    setEditFormData(prev => ({...prev, [e.target.name]: e.target.value}));
  };

  const submitEdit = (e) => {
    e.preventDefault();
    updateClientMutation.mutate(editFormData);
  };

  const handleManualChange = (e) => {
    setManualFormData(prev => ({...prev, [e.target.name]: e.target.value}));
  };

  const handleManualSubmit = (e) => {
    e.preventDefault();
    saveClientMutation.mutate(manualFormData);
    setManualFormData({});
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <div>
          <h1 className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">Client Identity</h1>
          <p className="text-slate-500 mt-1 uppercase text-[10px] font-bold tracking-widest">Portfolio Context & AI Strategy Hub</p>
        </div>
        <button 
            onClick={() => setShowAdd(!showAdd)}
            className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-all shadow-sm shadow-blue-200 flex items-center gap-2"
        >
          <span>+ Add Client</span>
        </button>
      </div>

      {showAdd && (
        <div className="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
            <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-semibold text-slate-800">Add New Client</h2>
                <div className="flex bg-slate-100 p-1 rounded-lg">
                    <button type="button" onClick={() => setIsManual(false)} className={`px-3 py-1 text-sm font-medium rounded-md transition-all ${!isManual ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700'}`}>AI Discovery</button>
                    <button type="button" onClick={() => setIsManual(true)} className={`px-3 py-1 text-sm font-medium rounded-md transition-all ${isManual ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700'}`}>Manual Entry</button>
                </div>
            </div>

            {!isManual ? (
                <>
                    <form onSubmit={handleDiscover} className="flex gap-4">
                        <input 
                            type="url" 
                            placeholder="Enter Client Website URL (e.g. https://example.com)" 
                            className="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                            value={url}
                            onChange={(e) => setUrl(e.target.value)}
                            required
                        />
                        <button 
                          type="submit" 
                          disabled={discoverMutation.isPending || saveClientMutation.isPending}
                          className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-medium transition-all disabled:opacity-50"
                        >
                          {discoverMutation.isPending ? 'Crawling...' : 'Extract Strategy'}
                        </button>
                    </form>
                    <p className="text-sm text-slate-500 mt-3 flex items-center gap-2">
                        <span className="w-1.5 h-1.5 rounded-full bg-blue-500"></span> 
                        The AI will read the website and automatically generate the Tone of Voice, Pain Points, and Target Audience.
                    </p>
                </>
            ) : (
                <form onSubmit={handleManualSubmit} className="flex flex-col gap-3">
                    <div className="grid grid-cols-2 gap-3">
                        <input name="name" placeholder="Client Name *" value={manualFormData.name || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" required />
                        <input name="website_url" type="url" placeholder="Website URL" value={manualFormData.website_url || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <input name="industry" placeholder="Industry" value={manualFormData.industry || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />
                        <input name="goals" placeholder="Primary Goals" value={manualFormData.goals || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />
                    </div>
                    
                    <div className="grid grid-cols-2 gap-3">
                        <textarea name="target_audience" placeholder="Target Audience" value={manualFormData.target_audience || ''} onChange={handleManualChange} rows={2} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500"></textarea>
                        <textarea name="target_audience_demographics" placeholder="Demographics (Age, Location, etc.)" value={manualFormData.target_audience_demographics || ''} onChange={handleManualChange} rows={2} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <textarea name="pain_points" placeholder="Pain Points Solved" value={manualFormData.pain_points || ''} onChange={handleManualChange} rows={2} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500"></textarea>
                        <textarea name="brand_voice" placeholder="Brand Voice" value={manualFormData.brand_voice || ''} onChange={handleManualChange} rows={2} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <input name="location" placeholder="City / Base Location (e.g. Austin, TX)" value={manualFormData.location || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />
                        <input name="service_area" placeholder="Service Areas / Neighborhoods" value={manualFormData.service_area || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />
                    </div>

                    <input name="competitors" placeholder="Competitors (comma separated)" value={manualFormData.competitors || ''} onChange={handleManualChange} className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-blue-500" />

                    <button type="submit" disabled={saveClientMutation.isPending} className="mt-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all w-max shadow-sm shadow-blue-200">
                        {saveClientMutation.isPending ? 'Saving...' : 'Save Client Manually'}
                    </button>
                </form>
            )}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {isLoading ? (
            <div className="p-5 text-center col-span-full font-medium text-slate-500">Loading clients...</div>
        ) : clients.length === 0 ? (
            <div className="p-6 text-center col-span-full border-2 border-dashed border-slate-200 rounded-xl bg-white text-slate-500">
                No clients found. Add your first client to unlock the workflow.
            </div>
        ) : (
            clients.map(client => (
                editingClient?.id === client.id ? (
                  <form key={client.id} onSubmit={submitEdit} className="bg-white rounded-xl p-4 shadow-sm border border-blue-200 flex flex-col gap-3">
                    <div className="flex justify-between items-center mb-2">
                       <h3 className="font-bold text-lg text-slate-900">Edit Client</h3>
                       <button type="button" onClick={() => setEditingClient(null)} className="text-slate-400 hover:text-slate-600 text-sm">Cancel</button>
                    </div>
                    
                    <div className="grid grid-cols-2 gap-3">
                        <input name="name" value={editFormData.name || ''} onChange={handleEditChange} placeholder="Client Name" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" required />
                        <input name="website_url" type="url" value={editFormData.website_url || ''} onChange={handleEditChange} placeholder="Website URL" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <input name="industry" value={editFormData.industry || ''} onChange={handleEditChange} placeholder="Industry" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />
                        <input name="goals" value={editFormData.goals || ''} onChange={handleEditChange} placeholder="Primary Goals" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />
                    </div>
                    
                    <div className="grid grid-cols-2 gap-3">
                        <textarea name="target_audience" value={editFormData.target_audience || ''} onChange={handleEditChange} placeholder="Target Audience" rows={2} className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"></textarea>
                        <textarea name="target_audience_demographics" value={editFormData.target_audience_demographics || ''} onChange={handleEditChange} placeholder="Demographics" rows={2} className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <textarea name="pain_points" value={editFormData.pain_points || ''} onChange={handleEditChange} placeholder="Pain Points Solved" rows={2} className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"></textarea>
                        <textarea name="brand_voice" value={editFormData.brand_voice || ''} onChange={handleEditChange} placeholder="Brand Voice" rows={2} className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"></textarea>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <input name="location" value={editFormData.location || ''} onChange={handleEditChange} placeholder="City / Base Location" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />
                        <input name="service_area" value={editFormData.service_area || ''} onChange={handleEditChange} placeholder="Service Areas / Neighborhoods" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />
                    </div>

                    <input name="competitors" value={editFormData.competitors || ''} onChange={handleEditChange} placeholder="Competitors" className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500" />

                    <button type="submit" disabled={updateClientMutation.isPending} className="w-full py-2 mt-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors text-sm disabled:opacity-50">
                        {updateClientMutation.isPending ? 'Saving...' : 'Save Changes'}
                    </button>
                  </form>
                ) : (
                  <div key={client.id} className="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:border-blue-200 hover:shadow-md transition-all group flex flex-col">
                     <div className="flex justify-between items-start mb-4">
                        <div className="flex gap-4 items-center">
                            <div className="w-10 h-10 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-base group-hover:bg-blue-600 group-hover:text-white transition-colors border border-slate-200">
                                {client.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h3 className="font-bold text-lg text-slate-900">{client.name}</h3>
                                {(client.website_url || client.industry) && (
                                    <div className="flex items-center gap-2 text-xs text-slate-500 font-medium">
                                        {client.industry && <span>{client.industry}</span>}
                                        {client.industry && client.website_url && <span>•</span>}
                                        {client.website_url && <a href={client.website_url} target="_blank" rel="noreferrer" className="text-blue-500 hover:underline">Link</a>}
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className="flex gap-2">
                           <button onClick={() => startEditing(client)} className="p-3 bg-white hover:bg-slate-900 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Edit">
                               <Pencil size={18} />
                           </button>
                           <button onClick={() => { if(confirm('Are you sure you want to delete this client?')) deleteClientMutation.mutate(client.id) }} className="p-3 bg-white hover:bg-rose-600 hover:text-white text-slate-400 rounded-xl border border-slate-100 shadow-xl transition-all" title="Delete">
                               <Trash2 size={18} />
                           </button>
                        </div>
                     </div>
                     
                     <div className="space-y-4 mb-6 flex-1 text-sm bg-slate-50 p-4 rounded-xl border border-slate-100 divide-y divide-slate-100">
                         <div>
                            <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Target Audience & Demographics</div>
                            <div className="text-slate-700 line-clamp-2">{client.target_audience} {client.target_audience_demographics && `— ${client.target_audience_demographics}`}</div>
                         </div>
                         <div className="pt-3">
                            <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pain Points Solved</div>
                            <div className="text-slate-700 line-clamp-2">{client.pain_points || 'None specified'}</div>
                         </div>
                         <div className="pt-3">
                            <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Brand Voice</div>
                            <div className="text-slate-700 line-clamp-2">{client.brand_voice || 'None specified'}</div>
                         </div>
                         {(client.goals || client.competitors || client.location || client.service_area) && (
                            <div className="pt-3 flex flex-col gap-1 text-[11px] text-slate-500">
                                {client.goals && <div><strong>Primary Goals:</strong> <span className="line-clamp-1 text-slate-600 font-medium">{client.goals}</span></div>}
                                {client.location && <div><strong>Map Base:</strong> <span className="line-clamp-1 text-blue-600 font-medium">{client.location}</span></div>}
                                {client.service_area && <div><strong>Local Reach:</strong> <span className="line-clamp-1 text-slate-600 font-medium">{client.service_area}</span></div>}
                                {client.competitors && <div><strong>Competitors (Vs):</strong> <span className="line-clamp-1 text-slate-600 font-medium">{client.competitors}</span></div>}
                            </div>
                         )}
                     </div>
  
                     <button onClick={() => {/* Navigate to pillars would go here if routed */}} className="w-full py-2.5 bg-white border border-slate-200 hover:border-blue-200 hover:text-blue-600 text-slate-600 rounded-lg font-bold text-[10px] uppercase tracking-widest transition-all">
                         View Strategy Map →
                     </button>
                  </div>
                )
            ))
        )}
      </div>
    </div>
  );
}
