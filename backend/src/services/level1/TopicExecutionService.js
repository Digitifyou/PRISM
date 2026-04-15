const ResearcherService = require('./ResearcherService');
const StrategistService = require('./StrategistService');
const WriterService = require('./WriterService');
const ImageGeneratorService = require('../level2/ImageGeneratorService');

class TopicExecutionService {
  async execute(plan, topic) {
    console.log(`\n--- TopicExecutionService: Processing topic: ${topic} ---`);
    
    try {
      // Step 1: Deep Research
      const research = await ResearcherService.research(topic);
      
      // Step 2: Content Strategy
      const strategy = await StrategistService.analyze(plan, topic, research);
      
      // Step 3: Copywriting (Creates Post objects in DB)
      const posts = await WriterService.write(plan, topic, research, strategy);

      // Step 4: Poster Generation (DISABLED AUTOMATICALLY)
      // We now wait for user approval of copy in the Drafts UI 
      // before triggering visual generation manually.
      
      console.log(`TopicExecutionService: Posts created for topic: ${topic}. Awaiting visual generation trigger.`);
      return posts;
    } catch (e) {
      console.error(`TopicExecutionService: Failed on topic [${topic}]:`, e.message);
      throw e;
    }
  }
}

module.exports = new TopicExecutionService();
