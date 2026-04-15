const axios = require('axios');
const { Setting } = require('../../models');

class LinkedInPublisher {
  async publish(post) {
    const tokenSetting = await Setting.findOne({ where: { key: 'linkedin_access_token' } });
    const personIdSetting = await Setting.findOne({ where: { key: 'linkedin_person_id' } });
    
    const accessToken = tokenSetting?.value || process.env.LINKEDIN_ACCESS_TOKEN;
    const personId = personIdSetting?.value || process.env.LINKEDIN_PERSON_ID;

    if (!accessToken || !personId) {
      throw new Error('LinkedIn credentials not configured. Add them in Settings.');
    }

    const author = `urn:li:person:${personId}`;

    let shareContent = {
      shareCommentary: { text: post.caption },
      shareMediaCategory: 'NONE'
    };

    if (post.image_url) {
      try {
        // LinkedIn requires the image data to be uploaded to their asset servers first
        const assetUrn = await this.uploadImage(post.image_url, author, accessToken);
        shareContent.shareMediaCategory = 'IMAGE';
        shareContent.media = [{
          status: 'READY',
          description: { text: post.topic },
          media: assetUrn,
          title: { text: post.topic }
        }];
      } catch (e) {
         console.error('LinkedIn image upload failed, posting without image.', e.message);
      }
    }

    const payload = {
      author: author,
      lifecycleState: 'PUBLISHED',
      specificContent: {
        'com.linkedin.ugc.ShareContent': shareContent
      },
      visibility: {
        'com.linkedin.ugc.MemberNetworkVisibility': 'PUBLIC'
      }
    };

    try {
      const response = await axios.post('https://api.linkedin.com/v2/ugcPosts', payload, {
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'X-Restli-Protocol-Version': '2.0.0',
          'Content-Type': 'application/json'
        }
      });

      return response.headers['x-restli-id'] || response.data.id || 'unknown';
    } catch (e) {
      const errorMsg = e.response?.data?.message || e.message;
      throw new Error(`LinkedIn API error: ${errorMsg}`);
    }
  }

  async uploadImage(imageUrl, owner, accessToken) {
    // Step 1: Register upload
    try {
      const registerResponse = await axios.post('https://api.linkedin.com/v2/assets?action=registerUpload', {
        registerUploadRequest: {
          recipes: ['urn:li:digitalmediaRecipe:feedshare-image'],
          owner: owner,
          serviceRelationships: [{
            relationshipType: 'OWNER',
            identifier: 'urn:li:userGeneratedContent'
          }]
        }
      }, {
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'X-Restli-Protocol-Version': '2.0.0',
          'Content-Type': 'application/json'
        }
      });

      const uploadUrl = registerResponse.data?.value?.uploadMechanism?.['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']?.uploadUrl;
      const assetUrn = registerResponse.data?.value?.asset;

      if (!uploadUrl || !assetUrn) {
        throw new Error('LinkedIn asset registration response missing uploadUrl or asset URN.');
      }

      // Step 2: Upload binary
      // If imageUrl is a local app path, we would need to read it via fs instead.
      let imageData;
      if (imageUrl.startsWith('http')) {
         const imageResponse = await axios.get(imageUrl, { responseType: 'arraybuffer', timeout: 30000 });
         imageData = imageResponse.data;
      } else {
         // Fallback for local paths if needed, but usually images are passed as full URLs or served locally via http://127.0.0.1
         throw new Error("Local relative image URLs are unsupported for LinkedIn publish without a host.");
      }
      
      await axios.put(uploadUrl, imageData, {
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Content-Type': 'application/octet-stream'
        }
      });

      return assetUrn;
    } catch (e) {
      const errorMsg = e.response?.data?.message || e.message;
      throw new Error(`LinkedIn image upload failed: ${errorMsg}`);
    }
  }
}

module.exports = new LinkedInPublisher();
