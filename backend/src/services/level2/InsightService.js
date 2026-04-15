const axios = require('axios');
const { Setting } = require('../../models');

class InsightService {
  async fetch(post) {
    if (!post.platform_post_id) {
      throw new Error(`Post #${post.id} has no platform_post_id. Publish it first.`);
    }

    switch (post.platform) {
      case 'facebook': return await this.fetchFacebook(post);
      case 'instagram': return await this.fetchInstagram(post);
      case 'linkedin': return await this.fetchLinkedIn(post);
      case 'google_business': return await this.fetchGoogleBusiness(post);
      default: throw new Error(`Unknown platform: ${post.platform}`);
    }
  }

  async fetchFacebook(post) {
    const tokenSetting = await Setting.findOne({ where: { key: 'facebook_access_token' } });
    const token = tokenSetting?.value || process.env.FACEBOOK_ACCESS_TOKEN;
    const postId = post.platform_post_id;

    try {
      const response = await axios.get(`https://graph.facebook.com/v19.0/${postId}/insights`, {
        params: {
          metric: 'post_impressions,post_reach,post_engaged_users',
          access_token: token
        }
      });

      const data = response.data.data || [];
      const impressionsStr = data.find(d => d.name === 'post_impressions');
      const reachStr = data.find(d => d.name === 'post_reach');
      const engagedStr = data.find(d => d.name === 'post_engaged_users');

      const impressions = impressionsStr?.values?.[0]?.value || 0;
      const reach = reachStr?.values?.[0]?.value || 0;
      const engaged = engagedStr?.values?.[0]?.value || 0;

      const reactResponse = await axios.get(`https://graph.facebook.com/v19.0/${postId}`, {
        params: {
          fields: 'likes.summary(true),comments.summary(true),shares',
          access_token: token
        }
      });

      const likes = reactResponse.data?.likes?.summary?.total_count || 0;
      const comments = reactResponse.data?.comments?.summary?.total_count || 0;
      const shares = reactResponse.data?.shares?.count || 0;

      return {
        likes,
        comments,
        shares,
        reach,
        impressions,
        engagement_rate: reach > 0 ? parseFloat(((engaged / reach) * 100).toFixed(2)) : 0
      };
    } catch (e) {
        console.error("Facebook Insights Error:", e.response?.data || e.message);
        return { likes: 0, comments: 0, shares: 0, reach: 0, impressions: 0, engagement_rate: 0 };
    }
  }

  async fetchInstagram(post) {
    const tokenSetting = await Setting.findOne({ where: { key: 'instagram_access_token' } });
    const token = tokenSetting?.value || process.env.INSTAGRAM_ACCESS_TOKEN;
    const postId = post.platform_post_id;

    try {
      const response = await axios.get(`https://graph.facebook.com/v19.0/${postId}/insights`, {
        params: {
          metric: 'impressions,reach,likes,comments,shares,saved',
          access_token: token
        }
      });

      const data = response.data.data || [];
      const getVal = (name) => {
        const item = data.find(d => d.name === name);
        return item?.values?.[0]?.value || 0;
      };

      const impressions = getVal('impressions');
      const reach = getVal('reach');
      const likes = getVal('likes');
      const comments = getVal('comments');
      const shares = getVal('shares');

      const totalEng = likes + comments + shares;

      return {
        likes,
        comments,
        shares,
        reach,
        impressions,
        engagement_rate: reach > 0 ? parseFloat(((totalEng / reach) * 100).toFixed(2)) : 0
      };
    } catch (e) {
      console.error("Instagram Insights Error:", e.response?.data || e.message);
      return { likes: 0, comments: 0, shares: 0, reach: 0, impressions: 0, engagement_rate: 0 };
    }
  }

  async fetchLinkedIn(post) {
    const tokenSetting = await Setting.findOne({ where: { key: 'linkedin_access_token' } });
    const token = tokenSetting?.value || process.env.LINKEDIN_ACCESS_TOKEN;
    const postId = post.platform_post_id;

    try {
      const response = await axios.get(`https://api.linkedin.com/v2/socialActions/${postId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'X-Restli-Protocol-Version': '2.0.0'
        }
      });

      const likes = response.data?.likesSummary?.totalLikes || 0;
      const comments = response.data?.commentsSummary?.totalFirstLevelComments || 0;
      const shares = response.data?.shareStatistics?.shareCount || 0;

      const statsResponse = await axios.get('https://api.linkedin.com/v2/organizationalEntityShareStatistics', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'X-Restli-Protocol-Version': '2.0.0'
        },
        params: {
          q: 'organizationalEntity',
          ugcPosts: `List(${postId})`
        }
      });

      const el = statsResponse.data?.elements?.[0]?.totalShareStatistics || {};
      const impressions = el.impressionCount || 0;
      const reach = el.uniqueImpressionsCount || 0;
      const totalEng = likes + comments + shares;

      return {
        likes,
        comments,
        shares,
        reach,
        impressions,
        engagement_rate: impressions > 0 ? parseFloat(((totalEng / impressions) * 100).toFixed(2)) : 0
      };
    } catch (e) {
       console.error("LinkedIn Insights Error:", e.response?.data || e.message);
       return { likes: 0, comments: 0, shares: 0, reach: 0, impressions: 0, engagement_rate: 0 };
    }
  }

  async fetchGoogleBusiness(post) {
    const tokenSetting = await Setting.findOne({ where: { key: 'google_business_token' } });
    const token = tokenSetting?.value || process.env.GOOGLE_BUSINESS_TOKEN;
    const postId = post.platform_post_id; // format: "locations/{id}/localPosts/{postId}"

    // GMB Insights are often location-wide, but we can fetch specific post views if available
    // For now, we return 0s or placeholder logic to ensure the UI doesn't crash
    return {
      likes: 0, 
      comments: 0, 
      shares: 0, 
      reach: 0, 
      impressions: 0, 
      engagement_rate: 0 
    };
  }
}

module.exports = new InsightService();
