const { DataTypes } = require('sequelize');
const { sequelize } = require('../config/database');

const Client = sequelize.define('Client', {
  name: { type: DataTypes.STRING, allowNull: false },
  website_url: { type: DataTypes.STRING, allowNull: true },
  industry: { type: DataTypes.STRING, allowNull: true },
  brand_voice: { type: DataTypes.STRING, allowNull: true },
  target_audience: { type: DataTypes.TEXT, allowNull: true },
  goals: { type: DataTypes.TEXT, allowNull: true },
  competitors: { type: DataTypes.TEXT, allowNull: true },
  target_audience_demographics: { type: DataTypes.TEXT, allowNull: true },
  pain_points: { type: DataTypes.TEXT, allowNull: true },
  location: { type: DataTypes.STRING, allowNull: true },
  service_area: { type: DataTypes.TEXT, allowNull: true },
  social_links: { type: DataTypes.JSON, allowNull: true },
}, { tableName: 'clients', timestamps: true });

const ClientPillar = sequelize.define('ClientPillar', {
  client_id: { type: DataTypes.INTEGER, allowNull: false },
  title: { type: DataTypes.STRING, allowNull: false },
  description: { type: DataTypes.TEXT, allowNull: true },
}, { tableName: 'client_pillars', timestamps: true });

const ContentPlan = sequelize.define('ContentPlan', {
  client_id: { type: DataTypes.INTEGER, allowNull: false },
  client_pillar_id: { type: DataTypes.INTEGER, allowNull: false },
  niche: { type: DataTypes.STRING, allowNull: true },
  topics: { type: DataTypes.JSON, allowNull: true },
  frequency: { type: DataTypes.STRING, allowNull: true },
  platforms: { type: DataTypes.JSON, allowNull: true },
  ai_provider: { type: DataTypes.STRING, allowNull: true },
  status: { type: DataTypes.STRING, defaultValue: 'processing' },
}, { tableName: 'content_plans', timestamps: true });

const Post = sequelize.define('Post', {
  client_id: { type: DataTypes.INTEGER, allowNull: false },
  content_plan_id: { type: DataTypes.INTEGER, allowNull: true },
  topic: { type: DataTypes.STRING, allowNull: true },
  platform: { type: DataTypes.STRING, allowNull: true },
  caption: { type: DataTypes.TEXT, allowNull: true },
  poster_copy: { type: DataTypes.TEXT, allowNull: true },
  image_url: { type: DataTypes.STRING, allowNull: true },
  image_prompt: { type: DataTypes.TEXT, allowNull: true },
  status: { type: DataTypes.STRING, defaultValue: 'draft' },
  research_data: { type: DataTypes.TEXT, allowNull: true },
  strategy_notes: { type: DataTypes.TEXT, allowNull: true },
  scheduled_at: { type: DataTypes.DATE, allowNull: true },
  published_at: { type: DataTypes.DATE, allowNull: true },
  platform_post_id: { type: DataTypes.STRING, allowNull: true },
  failure_reason: { type: DataTypes.TEXT, allowNull: true },
}, { tableName: 'posts', timestamps: true });

const Insight = sequelize.define('Insight', {
  post_id: { type: DataTypes.INTEGER, allowNull: false },
  platform: { type: DataTypes.STRING, allowNull: false },
  likes: { type: DataTypes.INTEGER, defaultValue: 0 },
  comments: { type: DataTypes.INTEGER, defaultValue: 0 },
  shares: { type: DataTypes.INTEGER, defaultValue: 0 },
  reach: { type: DataTypes.INTEGER, defaultValue: 0 },
  impressions: { type: DataTypes.INTEGER, defaultValue: 0 },
  engagement_rate: { type: DataTypes.DECIMAL(5, 2), defaultValue: 0.00 },
  fetched_at: { type: DataTypes.DATE, allowNull: true },
}, { tableName: 'insights', timestamps: true });

const Setting = sequelize.define('Setting', {
  key: { type: DataTypes.STRING, unique: true, allowNull: false },
  value: { type: DataTypes.TEXT, allowNull: true },
  label: { type: DataTypes.STRING, allowNull: true },
  group: { type: DataTypes.STRING, allowNull: true },
}, { tableName: 'settings', timestamps: true });


// Relationships
Client.hasMany(ClientPillar, { foreignKey: 'client_id', as: 'pillars' });
ClientPillar.belongsTo(Client, { foreignKey: 'client_id' });

Client.hasMany(ContentPlan, { foreignKey: 'client_id', as: 'content_plans' });
ContentPlan.belongsTo(Client, { foreignKey: 'client_id' });

ClientPillar.hasMany(ContentPlan, { foreignKey: 'client_pillar_id', as: 'content_plans' });
ContentPlan.belongsTo(ClientPillar, { foreignKey: 'client_pillar_id', as: 'pillar' });

ContentPlan.hasMany(Post, { foreignKey: 'content_plan_id', as: 'posts', onDelete: 'SET NULL' });
Post.belongsTo(ContentPlan, { foreignKey: 'content_plan_id' });

Client.hasMany(Post, { foreignKey: 'client_id' });
Post.belongsTo(Client, { foreignKey: 'client_id' });

Post.hasMany(Insight, { foreignKey: 'post_id', as: 'insights' });
Insight.belongsTo(Post, { foreignKey: 'post_id' });

module.exports = {
  Client,
  ClientPillar,
  ContentPlan,
  Post,
  Insight,
  Setting
};
