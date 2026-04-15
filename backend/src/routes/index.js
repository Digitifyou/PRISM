const express = require('express');
const router = express.Router();
const { Client, ClientPillar, ContentPlan, Post, Insight, Setting } = require('../models');
const ClientDiscoveryService = require('../services/level1/ClientDiscoveryService');
const PillarGeneratorService = require('../services/level1/PillarGeneratorService');
const { dispatchContentPlan } = require('../jobs/RunContentPlanJob');
const upload = require('../config/multer');
const ImageGeneratorService = require('../services/level2/ImageGeneratorService');
 
// --- WEBHOOKS (Prism Identity) ---
router.get('/webhooks/facebook', (req, res) => {
  const mode = req.query['hub.mode'];
  const token = req.query['hub.verify_token'];
  const challenge = req.query['hub.challenge'];

  const VERIFY_TOKEN = 'prism_secure_handshake_2024';

  if (mode === 'subscribe' && token === VERIFY_TOKEN) {
    console.log('WEBHOOK_VERIFIED');
    res.status(200).send(challenge);
  } else {
    res.sendStatus(403);
  }
});

router.post('/webhooks/facebook', (req, res) => {
  // Catch notifications here (e.g. comments, mentions)
  console.log('WEBHOOK_EVENT_RECEIVED:', JSON.stringify(req.body, null, 2));
  res.sendStatus(200);
});

// --- CLIENTS ---
router.get('/clients', async (req, res) => {
  const clients = await Client.findAll({ order: [['updated_at', 'DESC']] });
  res.json(clients);
});
router.post('/clients', async (req, res) => {
  const client = await Client.create(req.body);
  res.json(client);
});
router.post('/clients/discover', async (req, res) => {
  const { website_url } = req.body;
  if (!website_url) return res.status(400).json({ error: 'website_url is required' });
  const data = await ClientDiscoveryService.discover(website_url);
  res.json(data);
});
router.put('/clients/:id', async (req, res) => {
  const client = await Client.findByPk(req.params.id);
  if (!client) return res.status(404).json({ error: 'Client not found' });
  await client.update(req.body);
  res.json(client);
});
router.delete('/clients/:id', async (req, res) => {
  const client = await Client.findByPk(req.params.id);
  if (client) await client.destroy();
  res.json({ success: true });
});

// --- PILLARS ---
router.get('/pillars', async (req, res) => {
  const { client_id } = req.query;
  const where = client_id ? { client_id } : {};
  const pillars = await ClientPillar.findAll({ 
    where,
    include: ['Client'], 
    order: [['updated_at', 'DESC']] 
  });
  res.json(pillars);
});
router.get('/pillars/client/:clientId', async (req, res) => {
  const pillars = await ClientPillar.findAll({ where: { client_id: req.params.clientId } });
  res.json(pillars);
});
router.post('/pillars', async (req, res) => {
  const pillar = await ClientPillar.create(req.body);
  res.json(pillar);
});
router.put('/pillars/:id', async (req, res) => {
  const pillar = await ClientPillar.findByPk(req.params.id);
  if (!pillar) return res.status(404).json({ error: 'Pillar not found' });
  await pillar.update(req.body);
  res.json(pillar);
});
router.delete('/pillars/:id', async (req, res) => {
  const pillar = await ClientPillar.findByPk(req.params.id);
  if (pillar) await pillar.destroy();
  res.json({ success: true });
});
router.post('/pillars/generate', async (req, res) => {
  const { client_id } = req.body;
  const client = await Client.findByPk(client_id);
  if (!client) return res.status(404).json({ error: 'Client not found' });
  const pillars = await PillarGeneratorService.generate(client);
  // Bulk store them immediately unlike PHP which showed a preview
  for(let p of pillars) {
      await ClientPillar.create({ client_id, title: p.title, description: p.description });
  }
  res.json({ success: true, count: pillars.length });
});

const TopicExecutionService = require('../services/level1/TopicExecutionService');

// --- CONTENT PLANS ---
router.get('/plans', async (req, res) => {
  const { client_id } = req.query;
  const where = client_id ? { client_id } : {};
  const plans = await ContentPlan.findAll({ 
    where,
    include: ['Client', 'pillar', 'posts'], 
    order: [['updated_at', 'DESC']] 
  });
  res.json(plans);
});

router.post('/plans', async (req, res) => {
  try {
    const planData = { topics: [], ...req.body };
    const plan = await ContentPlan.create(planData);
    plan.client = await Client.findByPk(plan.client_id);
    dispatchContentPlan(plan);
    res.json(plan);
  } catch (e) {
    console.error('Error creating plan:', e);
    res.status(500).json({ error: e.message, details: e.errors });
  }
});
router.put('/plans/:id', async (req, res) => {
  const plan = await ContentPlan.findByPk(req.params.id);
  if (!plan) return res.status(404).json({ error: 'Plan not found' });
  await plan.update(req.body);
  res.json(plan);
});

router.delete('/plans/:id', async (req, res) => {
  const plan = await ContentPlan.findByPk(req.params.id);
  if (plan) await plan.destroy();
  res.json({ success: true });
});

router.post('/plans/:id/generate-topic', async (req, res) => {
    const { topic } = req.body;
    const plan = await ContentPlan.findByPk(req.params.id, { include: ['Client'] });
    if (!plan) return res.status(404).json({ error: 'Plan not found' });
    
    try {
        const posts = await TopicExecutionService.execute(plan, topic);
        res.json({ success: true, count: posts.length });
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

// --- POSTS ---
router.get('/posts', async (req, res) => {
  const { client_id } = req.query;
  let where = {};
  let include = ['ContentPlan'];
  
  if (client_id) {
    where = { client_id: client_id };
  }

  const posts = await Post.findAll({ 
    where,
    include,
    order: [['updated_at', 'DESC']] 
  });
  res.json(posts);
});
router.get('/posts/:id', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  res.json(post);
});
router.get('/posts/:id/insights', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (!post || post.status !== 'published') return res.status(400).json({ error: 'Post must be published' });
  try {
      const data = await require('../services/level2/InsightService').fetch(post);
      res.json(data);
  } catch(e) {
      res.status(500).json({ error: e.message });
  }
});
router.delete('/posts/:id', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (post) await post.destroy();
  res.json({ success: true });
});
router.patch('/posts/:id', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (post) await post.update(req.body);
  res.json(post);
});
router.patch('/posts/:id/approve', async (req, res) => {
  const { scheduled_at } = req.body;
  const post = await Post.findByPk(req.params.id);
  if (post) {
      await post.update({ 
          status: 'approved',
          scheduled_at: scheduled_at || null 
      });
  }
  res.json(post);
});
router.patch('/posts/:id/reject', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (post) await post.update({ status: 'failed', failure_reason: 'Rejected by user' });
  res.json(post);
});

router.patch('/posts/:id/refine', async (req, res) => {
  const { instruction } = req.body;
  const post = await Post.findByPk(req.params.id);
  if (!post) return res.status(404).json({ error: 'Post not found' });
  
  try {
      const refined = await require('../services/level2/RefinementService').refine(post, instruction);
      res.json(refined);
  } catch (e) {
      res.status(500).json({ error: e.message });
  }
});

router.post('/posts/:id/publish', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (!post) return res.status(404).json({ error: 'Post not found' });
  
  if (post.status !== 'approved') return res.status(400).json({ error: 'Post must be approved first' });

  try {
      let platformId = 'unknown';
      if (post.platform === 'facebook') platformId = await require('../services/level2/FacebookPublisher').publish(post);
      if (post.platform === 'instagram') platformId = await require('../services/level2/InstagramPublisher').publish(post);
      if (post.platform === 'linkedin') platformId = await require('../services/level2/LinkedInPublisher').publish(post);
      if (post.platform === 'google_business') platformId = await require('../services/level2/GoogleBusinessPublisher').publish(post);
      
      await post.update({ status: 'published', platform_post_id: String(platformId), published_at: new Date() });
      res.json(post);
  } catch (e) {
      await post.update({ status: 'failed', failure_reason: e.message });
      res.status(500).json({ error: e.message });
  }
});

router.post('/posts/:id/upload-image', upload.single('image'), async (req, res) => {
    const post = await Post.findByPk(req.params.id);
    if (!post) return res.status(404).json({ error: 'Post not found' });
    if (!req.file) return res.status(400).json({ error: 'No image file uploaded' });

    const imageUrl = `/post-images/${req.file.filename}`;
    await post.update({ image_url: imageUrl });
    res.json(post);
});

router.post('/posts/:id/recomposite', async (req, res) => {
    const post = await Post.findByPk(req.params.id);
    if (!post) return res.status(404).json({ error: 'Post not found' });
    
    try {
        const PosterCompositeService = require('../services/level2/PosterCompositeService');
        // Use the current image_url as base
        const finalUrl = await PosterCompositeService.composite(post, post.image_url);
        await post.update({ image_url: finalUrl });
        res.json(post);
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

router.post('/posts/:id/regenerate-image', async (req, res) => {
    const post = await Post.findByPk(req.params.id);
    if (!post) return res.status(404).json({ error: 'Post not found' });

    try {
        await ImageGeneratorService.generate(post);
        res.json(post);
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

router.delete('/posts/:id', async (req, res) => {
  const post = await Post.findByPk(req.params.id);
  if (post) await post.destroy();
  res.json({ success: true });
});

// --- SETTINGS ---
router.get('/settings', async (req, res) => {
  const settings = await Setting.findAll();
  res.json(settings);
});
router.post('/settings', async (req, res) => {
  const { settings } = req.body;
  if (settings && typeof settings === 'object') {
     for (const [key, value] of Object.entries(settings)) {
         let setting = await Setting.findOne({ where: { key } });
         if (setting) await setting.update({ value });
         else await Setting.create({ key, value });
     }
  }
  res.json({ success: true });
});

router.post('/settings', async (req, res) => {
  // ... existing settings code ...
});

// --- AUTHENTICATION (Prism Identity Hub) ---
router.post('/auth/facebook/link', async (req, res) => {
    const { accessToken } = req.body;
    if (!accessToken) return res.status(400).json({ error: 'accessToken is required' });

    try {
        const result = await require('../services/level2/AuthService').linkFacebook(accessToken);
        res.json(result);
    } catch (e) {
        res.status(500).json({ error: e.message });
    }
});

module.exports = router;
