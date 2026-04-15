const axios = require('axios');
const { Setting } = require('../../models');

class InstagramPublisher {
  constructor() {
    this.apiVersion = 'v19.0';
  }

  async publish(post) {
    const accountIdSetting = await Setting.findOne({ where: { key: 'instagram_account_id' } });
    const tokenSetting = await Setting.findOne({ where: { key: 'instagram_access_token' } });
    
    const accountId = accountIdSetting?.value || process.env.INSTAGRAM_ACCOUNT_ID;
    const accessToken = tokenSetting?.value || process.env.INSTAGRAM_ACCESS_TOKEN;

    if (!accountId || !accessToken) {
      throw new Error('Instagram credentials not configured. Add them in Settings.');
    }

    if (!post.image_url) {
      throw new Error('Instagram requires an image. This post has no image_url. Wait for image generation to complete or regenerate the image.');
    }

    const base = `https://graph.facebook.com/${this.apiVersion}`;
    
    try {
      // Step 1: Create media container
      const containerResponse = await axios.post(`${base}/${accountId}/media`, {
        image_url: post.image_url,
        caption: post.caption,
        access_token: accessToken
      });

      const creationId = containerResponse.data.id;
      if (!creationId) {
        throw new Error("Instagram did not return a creation_id.");
      }

      // Step 2: Publish the container
      const publishResponse = await axios.post(`${base}/${accountId}/media_publish`, {
        creation_id: creationId,
        access_token: accessToken
      });

      return publishResponse.data.id || 'unknown';
    } catch (e) {
      const errorMsg = e.response?.data?.error?.message || e.message;
      throw new Error(`Instagram API error: ${errorMsg}`);
    }
  }
}

module.exports = new InstagramPublisher();
