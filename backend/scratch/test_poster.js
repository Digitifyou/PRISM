const { Post, Client, ContentPlan } = require('../src/models');
const ImageGeneratorService = require('../src/services/level2/ImageGeneratorService');
const path = require('path');

async function testPoster() {
    console.log('--- Starting Poster Generation Test ---');
    
    try {
        // 1. Find a draft post
        const post = await Post.findOne({ 
            where: { status: 'draft' },
            include: ['ContentPlan']
        });

        if (!post) {
            console.error('No draft posts found. Please create a content plan first.');
            return;
        }

        console.log(`Testing with Post ID: ${post.id}`);
        console.log(`Topic: ${post.topic}`);
        console.log(`Poster Copy: ${post.poster_copy}`);

        // 2. Trigger Generation (which now includes Composition)
        console.log('Generating AI background and compositing typography...');
        await ImageGeneratorService.generate(post);

        // 3. Verify
        const updatedPost = await Post.findByPk(post.id);
        console.log('\n--- SUCCESS ---');
        console.log(`New Image URL: ${updatedPost.image_url}`);
        console.log(`Path: ${path.join(__dirname, '../public', updatedPost.image_url)}`);
        
    } catch (e) {
        console.error('Test Failed:', e);
    } finally {
        process.exit();
    }
}

testPoster();
