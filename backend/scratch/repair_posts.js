const { Post, ContentPlan, Client } = require('../src/models');

async function repairOrphanPosts() {
    console.log('--- Starting Post Data Repair ---');
    try {
        const posts = await Post.findAll({ 
            where: { client_id: null },
            include: [{ model: ContentPlan }]
        });

        console.log(`Found ${posts.length} posts missing client_id.`);

        for (const post of posts) {
            if (post.ContentPlan) {
                console.log(`Repairing Post ${post.id} -> Setting client_id to ${post.ContentPlan.client_id}`);
                await post.update({ client_id: post.ContentPlan.client_id });
            } else {
                // Fallback: Use the first client in the DB if absolutely orphaned
                const firstClient = await Client.findOne();
                if (firstClient) {
                    console.log(`Post ${post.id} is deeply orphaned. Falling back to Client ${firstClient.id}`);
                    await post.update({ client_id: firstClient.id });
                }
            }
        }

        console.log('\n--- DATA REPAIR COMPLETE ---');
    } catch (e) {
        console.error('Repair failed:', e);
    } finally {
        process.exit();
    }
}

repairOrphanPosts();
