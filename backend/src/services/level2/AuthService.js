const axios = require('axios');
const { Setting } = require('../../models');

class AuthService {
    constructor() {
        this.appId = process.env.FACEBOOK_APP_ID;
        this.appSecret = process.env.FACEBOOK_APP_SECRET;
        this.apiVersion = 'v19.0';
    }

    async linkFacebook(shortLivedToken) {
        try {
            // 1. Exchange for Long-Lived User Token (60 days)
            const longLivedRes = await axios.get(`https://graph.facebook.com/${this.apiVersion}/oauth/access_token`, {
                params: {
                    grant_type: 'fb_exchange_token',
                    client_id: this.appId,
                    client_secret: this.appSecret,
                    fb_exchange_token: shortLivedToken
                }
            });

            const userToken = longLivedRes.data.access_token;

            // 2. Fetch User's Pages to get Page ID and Permanent Page Token
            const pagesRes = await axios.get(`https://graph.facebook.com/${this.apiVersion}/me/accounts`, {
                params: { access_token: userToken }
            });

            const pages = pagesRes.data.data || [];
            if (pages.length === 0) {
                throw new Error('No Facebook Pages found for this account.');
            }

            // For now, we take the first page. Future: let user choose.
            const primaryPage = pages[0];
            const pageToken = primaryPage.access_token;
            const pageId = primaryPage.id;

            // 3. Save to Settings
            await this.saveSetting('facebook_access_token', pageToken);
            await this.saveSetting('facebook_page_id', pageId);

            // 4. Check for linked Instagram Account
            try {
                const igRes = await axios.get(`https://graph.facebook.com/${this.apiVersion}/${pageId}`, {
                    params: { 
                        fields: 'instagram_business_account',
                        access_token: pageToken 
                    }
                });

                if (igRes.data.instagram_business_account) {
                    const igId = igRes.data.instagram_business_account.id;
                    await this.saveSetting('instagram_account_id', igId);
                    await this.saveSetting('instagram_access_token', pageToken); // IG uses the Page token
                }
            } catch (igErr) {
                console.warn('Could not find linked Instagram account:', igErr.message);
            }

            return {
                page_name: primaryPage.name,
                page_id: pageId,
                has_instagram: !!this.getSetting('instagram_account_id')
            };

        } catch (e) {
            console.error('AuthService Error:', e.response?.data || e.message);
            throw new Error('Failed to exchange Facebook tokens: ' + (e.response?.data?.error?.message || e.message));
        }
    }

    async saveSetting(key, value) {
        let setting = await Setting.findOne({ where: { key } });
        if (setting) await setting.update({ value });
        else await Setting.create({ key, value });
    }

    async getSetting(key) {
        const s = await Setting.findOne({ where: { key } });
        return s?.value;
    }
}

module.exports = new AuthService();
