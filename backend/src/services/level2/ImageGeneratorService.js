const AiProviderFactory = require('../ai/AiProviderFactory');
const axios = require('axios');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const PosterCompositeService = require('./PosterCompositeService');

const FALLBACK_GALLERY = [
    'https://images.unsplash.com/photo-1497366216548-37526070297c', // Modern Office/Med
    'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d', // Clinical Bright
    'https://images.unsplash.com/photo-1504813184591-01572f98c85f', // Medical Tech
    'https://images.unsplash.com/photo-1516549655169-df83a0774514', // Abstract Professional
    'https://images.unsplash.com/photo-1504439468489-c8920d796a29', // Modern Interior
    'https://images.unsplash.com/photo-1538108149393-fdfd81691937', // Healthcare Clean
];

class ImageGeneratorService {
  async generate(post) {
    /*
    // Assuming post.getContentPlan() works via Sequelize association
    // Or we lazy load it if not available
    const plan = post.ContentPlan || await post.getContentPlan();
    if (!plan) throw new Error('Post is missing ContentPlan');

    const ai = await AiProviderFactory.make(plan.ai_provider);

    const platformSizes = {
      facebook: '1080x1350 portrait',
      instagram: '1080x1350 portrait',
      linkedin: '1080x1350 portrait',
      google_business: '1080x1350 portrait'
    };

    const sizeHint = platformSizes[post.platform] || '1200x630';
    const prompt = `Create a professional, eye-catching social media image for ${post.platform}. Topic: ${post.topic}. Niche: ${plan.niche || ''}. Size: ${sizeHint}. Style: modern, clean, vibrant, no text overlay. Suitable for a brand social media post.`;

    const result = await ai.generateImage(prompt).catch(e => {
        console.warn(`AI Image Generation failed: ${e.message}. Using diversified fallback.`);
        // Select a random image from the gallery so all posters don't look the same
        const randomIndex = Math.floor(Math.random() * FALLBACK_GALLERY.length);
        return `${FALLBACK_GALLERY[randomIndex]}?auto=format&fit=crop&w=1080&q=80`;
    });

    let finalBackgroundUrl = '';

    if (result.startsWith('data:image/')) {
        finalBackgroundUrl = this.storeBase64(result, post.id);
    } else {
        finalBackgroundUrl = await this.downloadAndStore(result, post.id);
    }

    // Step 2: Composite into a professional poster (Background + Typography)
    const compositeUrl = await PosterCompositeService.composite(post, finalBackgroundUrl);

    await post.update({
        image_url: compositeUrl,
        image_prompt: prompt
    });
    */
  }

  storeBase64(dataUri, postId) {
    const base64 = dataUri.substring(dataUri.indexOf(',') + 1);
    const buffer = Buffer.from(base64, 'base64');
    const folder = path.join(__dirname, '../../../public/post-images');
    if (!fs.existsSync(folder)) fs.mkdirSync(folder, { recursive: true });
    
    // To serve statically, we put it in backend/public
    const filename = `${postId}-${crypto.randomBytes(4).toString('hex')}.png`;
    fs.writeFileSync(path.join(folder, filename), buffer);
    
    return `/post-images/${filename}`;
  }

  async downloadAndStore(url, postId) {
    try {
      const response = await axios.get(url, { responseType: 'arraybuffer', timeout: 30000 });
      const folder = path.join(__dirname, '../../../public/post-images');
      if (!fs.existsSync(folder)) fs.mkdirSync(folder, { recursive: true });
      
      const filename = `${postId}-${crypto.randomBytes(4).toString('hex')}.jpg`;
      fs.writeFileSync(path.join(folder, filename), response.data);
      
      return `/post-images/${filename}`;
    } catch (e) {
      console.warn(`ImageGeneratorService: failed to download image from ${url}:`, e.message);
      return url; // fallback to original URL
    }
  }
}

module.exports = new ImageGeneratorService();
