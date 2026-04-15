const PlannerService = require('../services/level1/PlannerService');
const ResearcherService = require('../services/level1/ResearcherService');
const StrategistService = require('../services/level1/StrategistService');
const WriterService = require('../services/level1/WriterService');
const ImageGeneratorService = require('../services/level2/ImageGeneratorService');

class RunContentPlanJob {
  async handle(plan) {
    console.log(`RunContentPlanJob started for plan_id: ${plan.id}`);

    try {
      // Step 1: Generate topic list (Titles only)
      await PlannerService.generate(plan);

      console.log(`\nRunContentPlanJob Title generation completed for plan_id: ${plan.id}`);

      console.log(`\nRunContentPlanJob completed for plan_id: ${plan.id}`);
      await plan.update({ status: 'completed' });
    } catch(globalError) {
       console.error(`RunContentPlanJob failed fatally:`, globalError.message);
       await plan.update({ status: 'failed' });
    }
  }
}

// Simple in-memory background dispatcher
const dispatchContentPlan = (plan) => {
    const job = new RunContentPlanJob();
    // Fire and forget so we don't block API response
    setTimeout(() => {
        job.handle(plan);
    }, 100);
}

module.exports = { RunContentPlanJob, dispatchContentPlan };
