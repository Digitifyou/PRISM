const axios = require('axios');
const { Setting } = require('../../models');

class FacebookPublisher {
  constructor() {
    this.apiVersion = 'v19.0';
  }

  async publish(post) {
    const pageIdSetting = await Setting.findOne({ where: { key: 'facebook_page_id' } });
    const tokenSetting = await Setting.findOne({ where: { key: 'facebook_access_token' } });
    
    const pageId = pageIdSetting?.value || process.env.FACEBOOK_PAGE_ID;
    const accessToken = tokenSetting?.value || process.env.FACEBOOK_ACCESS_TOKEN;

    if (!pageId || !accessToken) {
      throw new Error('Facebook credentials not configured. Add them in Settings.');
    }

    const base = `https://graph.facebook.com/${this.apiVersion}`;
    
    try {
      let response;
      if (post.image_url) {
        // Facebook prefers URL/Caption as parameters or form data instead of raw JSON payload sometimes for photos
        response = await axios.post(`${base}/${pageId}/photos`, {
          url: post.image_url,
          caption: post.caption,
          access_token: accessToken
        });
      } else {
        response = await axios.post(`${base}/${pageId}/feed`, {
          message: post.caption,
          access_token: accessToken
        });
      }

      const data = response.data;
      return data.id || data.post_id || 'unknown';
    } catch (e) {
      const errorMsg = e.response?.data?.error?.message || e.message;
      throw new Error(`Facebook API error: ${errorMsg}`);
    }
  }
}

module.exports = new FacebookPublisher();
