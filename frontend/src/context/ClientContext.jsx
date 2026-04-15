import React, { createContext, useContext, useState, useEffect } from 'react';

const ClientContext = createContext();

export const ClientProvider = ({ children }) => {
    const [selectedClientId, setSelectedClientId] = useState(() => {
        return localStorage.getItem('selectedClientId') || '';
    });

    const [selectedClient, setSelectedClient] = useState(() => {
        const saved = localStorage.getItem('selectedClient');
        try {
            return saved ? JSON.parse(saved) : null;
        } catch (e) {
            return null;
        }
    });

    useEffect(() => {
        if (selectedClientId) {
            localStorage.setItem('selectedClientId', selectedClientId);
        } else {
            localStorage.removeItem('selectedClientId');
            localStorage.removeItem('selectedClient');
            setSelectedClient(null);
        }
    }, [selectedClientId]);

    useEffect(() => {
        if (selectedClient) {
            localStorage.setItem('selectedClient', JSON.stringify(selectedClient));
        }
    }, [selectedClient]);

    return (
        <ClientContext.Provider value={{ 
            selectedClientId, 
            setSelectedClientId, 
            selectedClient, 
            setSelectedClient 
        }}>
            {children}
        </ClientContext.Provider>
    );
};

export const useClient = () => {
    const context = useContext(ClientContext);
    if (!context) {
        throw new Error('useClient must be used within a ClientProvider');
    }
    return context;
};
