const { sequelize } = require('./src/config/database');

async function fix() {
  console.log('--- Database Repair: Restoring Plan Visibility ---');
  
  try {
    // 1. Ensure content_plan_id is NULLABLE (to support survival after deletion)
    console.log('1. Making content_plan_id nullable...');
    await sequelize.query(`ALTER TABLE posts MODIFY content_plan_id INT(11) NULL;`);
    
    // 2. Add client_id if it doesn't exist
    console.log('2. Adding client_id column to posts...');
    try {
        await sequelize.query(`ALTER TABLE posts ADD COLUMN client_id INT(11) AFTER id;`);
    } catch (e) {
        console.log('Column client_id likely already exists, skipping ADD.');
    }

    // 3. Update associations (SET NULL)
    console.log('3. Updating Foreign Key constraints...');
    // We try to add the FK that failed during the automated sync
    try {
        await sequelize.query(`ALTER TABLE posts ADD CONSTRAINT posts_content_plan_fk FOREIGN KEY (content_plan_id) REFERENCES content_plans (id) ON DELETE SET NULL;`);
    } catch (e) {
        console.log('FK already exists or fails, skipping.');
    }

    // 4. Backfill client_id for existing posts
    console.log('4. Backfilling client_id for existing drafts...');
    await sequelize.query(`
        UPDATE posts p
        JOIN content_plans cp ON p.content_plan_id = cp.id
        SET p.client_id = cp.client_id
        WHERE p.client_id IS NULL;
    `);

    console.log('--- Repair Complete! ---');
    process.exit(0);
  } catch (err) {
    console.error('FATAL REPAIR ERROR:', err);
    process.exit(1);
  }
}

fix();
