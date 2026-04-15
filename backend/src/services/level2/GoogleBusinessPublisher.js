const axios = require('axios');
const { Setting } = require('../../models');

class GoogleBusinessPublisher {
  constructor() {
    this.baseUrl = 'https://mybusiness.googleapis.com/v4';
  }

  async publish(post) {
    const locationIdSetting = await Setting.findOne({ where: { key: 'google_business_location_id' } });
    const tokenSetting = await Setting.findOne({ where: { key: 'google_business_token' } });
    
    // locationId should be in format "locations/{locationId}"
    const locationName = locationIdSetting?.value || process.env.GOOGLE_BUSINESS_LOCATION_ID;
    const accessToken = tokenSetting?.value || process.env.GOOGLE_BUSINESS_TOKEN;

    if (!locationName || !accessToken) {
      throw new Error('Google My Business credentials not configured. Add them in Settings.');
    }

    try {
      const payload = {
        languageCode: 'en-US',
        summary: post.caption,
        topicType: 'STANDARD'
      };

      // Handle Image if available
      if (post.image_url) {
        payload.media = [
          {
            mediaFormat: 'PHOTO',
            sourceUrl: post.image_url.startsWith('http') ? post.image_url : `http://localhost:3001${post.image_url}`
          }
        ];
      }

      // Handle CTA if applicable (derived from poster_copy or hardcoded for efficiency)
      payload.callToAction = {
        actionType: 'LEARN_MORE',
        url: 'https://google.com' // Placeholder or derived from client settings
      };

      const response = await axios.post(`${this.baseUrl}/${locationName}/localPosts`, payload, {
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Content-Type': 'application/json'
        }
      });

      return response.data.name || 'unknown';
    } catch (e) {
      const errorMsg = e.response?.data?.error?.message || e.message;
      throw new Error(`GMB API error: ${errorMsg}`);
    }
  }
}

module.exports = new GoogleBusinessPublisher();
