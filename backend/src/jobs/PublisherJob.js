const { Post } = require('../models');
const { Op } = require('sequelize');

/**
 * Prism Chronos: The Scheduling Watchman
 * Scans for approved posts that are due for publication.
 */
class PublisherJob {
    constructor() {
        this.interval = 60000; // Check every minute
        this.isRunning = false;
    }

    start() {
        console.log('--- Chronos Scheduling Engine Engaged ---');
        setInterval(() => this.tick(), this.interval);
        // Run first tick immediately
        this.tick();
    }

    async tick() {
        if (this.isRunning) return;
        this.isRunning = true;

        try {
            const now = new Date();
            
            // Find posts: Approved AND Scheduled At <= Now
            const duePosts = await Post.findAll({
                where: {
                    status: 'approved',
                    scheduled_at: {
                        [Op.lte]: now,
                        [Op.ne]: null
                    }
                }
            });

            if (duePosts.length > 0) {
                console.log(`[Chronos] Found ${duePosts.length} posts due for deployment.`);
                for (const post of duePosts) {
                    await this.publishPost(post);
                }
            }

        } catch (e) {
            console.error('[Chronos] Error in tick:', e.message);
        } finally {
            this.isRunning = false;
        }
    }

    async publishPost(post) {
        console.log(`[Chronos] Deploying Post ID ${post.id} to ${post.platform}...`);
        
        try {
            let platformId = 'unknown';

            // Resolve the specific publisher service
            if (post.platform === 'facebook') {
                platformId = await require('../services/level2/FacebookPublisher').publish(post);
            } else if (post.platform === 'instagram') {
                platformId = await require('../services/level2/InstagramPublisher').publish(post);
            } else if (post.platform === 'linkedin') {
                platformId = await require('../services/level2/LinkedInPublisher').publish(post);
            } else if (post.platform === 'google_business') {
                platformId = await require('../services/level2/GoogleBusinessPublisher').publish(post);
            }

            // Update post status to published
            await post.update({
                status: 'published',
                platform_post_id: String(platformId),
                published_at: new Date()
            });

            console.log(`[Chronos] Success: Post ID ${post.id} is now LIVE.`);

        } catch (e) {
            console.error(`[Chronos] Deployment failed for Post ID ${post.id}:`, e.message);
            await post.update({
                status: 'failed',
                failure_reason: 'Automated deployment failed: ' + e.message
            });
        }
    }
}

module.exports = new PublisherJob();
