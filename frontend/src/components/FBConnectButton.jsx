import React, { useState } from 'react';
import api from '../api';
import toast from 'react-hot-toast';
import SocialIcon from './SocialIcon';
import { Link as LinkIcon, CheckCircle2, Loader2 } from 'lucide-react';

export default function FBConnectButton({ onComplete }) {
    const [isConnecting, setIsConnecting] = useState(false);

    const handleLogin = () => {
        if (!window.FB) {
            toast.error('Facebook SDK not loaded. Check your internet or ad-blocker.');
            return;
        }

        setIsConnecting(true);
        window.FB.login((response) => {
            if (response.authResponse) {
                const accessToken = response.authResponse.accessToken;
                linkAccount(accessToken);
            } else {
                setIsConnecting(false);
                toast.error('User cancelled login or did not fully authorize.');
            }
        }, {
            // Prism requires these scopes for automated publishing and analytics
            scope: 'pages_manage_posts,pages_read_engagement,instagram_basic,instagram_content_publish,public_profile'
        });
    };

    const linkAccount = async (accessToken) => {
        try {
            toast.loading('Authenticating with Prism Server...', { id: 'auth' });
            const res = await api.post('/auth/facebook/link', { accessToken });
            toast.success('Facebook & Instagram Connected!', { id: 'auth' });
            if (onComplete) onComplete(res.data);
        } catch (e) {
            toast.error('Connection failed: ' + (e.response?.data?.error || e.message), { id: 'auth' });
        } finally {
            setIsConnecting(false);
        }
    };

    return (
        <button
            onClick={handleLogin}
            disabled={isConnecting}
            className={`flex items-center gap-3 px-6 py-3 rounded-xl font-bold text-sm tracking-widest uppercase transition-all shadow-lg ${
                isConnecting 
                ? 'bg-slate-100 text-slate-400 cursor-not-allowed' 
                : 'bg-[#1877F2] hover:bg-[#166fe5] text-white hover:scale-[1.02] active:scale-[0.98]'
            }`}
        >
            {isConnecting ? (
                <Loader2 size={18} className="animate-spin" />
            ) : (
                <SocialIcon platform="facebook" size={18} className="fill-white" />
            )}
            {isConnecting ? 'Establishing Link...' : 'Connect Facebook & Instagram'}
        </button>
    );
}
