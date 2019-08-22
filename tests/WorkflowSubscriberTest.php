<?php

namespace Tests {

    use Brexis\LaravelWorkflow\Events\AnnounceEvent;
    use Brexis\LaravelWorkflow\Events\CompletedEvent;
    use Brexis\LaravelWorkflow\Events\EnteredEvent;
    use Brexis\LaravelWorkflow\Events\EnterEvent;
    use Brexis\LaravelWorkflow\Events\GuardEvent;
    use Brexis\LaravelWorkflow\Events\LeaveEvent;
    use Brexis\LaravelWorkflow\Events\TransitionEvent;
    use PHPUnit\Framework\TestCase;
    use Brexis\LaravelWorkflow\WorkflowRegistry;
    use Tests\Fixtures\TestObject;

    class WorkflowSubscriberTest extends TestCase
    {
        public function testIfWorkflowEmitsEvents()
        {
            global $events;

            $events = [];

            $config = [
                'straight' => [
                    'supports'    => [TestObject::class],
                    'places'      => ['a', 'b', 'c'],
                    'transitions' => [
                        't1' => [
                            'from' => 'a',
                            'to'   => 'b',
                        ],
                        't2' => [
                            'from' => 'b',
                            'to'   => 'c',
                        ],
                    ],
                ],
            ];

            $registry = new WorkflowRegistry($config);
            $object = new TestObject;
            $workflow = $registry->get($object);

            $workflow->apply($object, 't1');

            $this->assertContains('workflow.guard', $events);
            $this->assertContains('workflow.straight.guard', $events);
            $this->assertContains('workflow.straight.guard.t1', $events);

            $this->assertContains('workflow.leave', $events);
            $this->assertContains('workflow.straight.leave', $events);
            $this->assertContains('workflow.straight.leave.a', $events);

            $this->assertContains('workflow.transition', $events);
            $this->assertContains('workflow.straight.transition', $events);
            $this->assertContains('workflow.straight.transition.t1', $events);

            $this->assertContains('workflow.enter', $events);
            $this->assertContains('workflow.straight.enter', $events);
            $this->assertContains('workflow.straight.enter.b', $events);

            $this->assertContains('workflow.entered', $events);
            $this->assertContains('workflow.straight.entered', $events);
            $this->assertContains('workflow.straight.entered.b', $events);

            $this->assertContains('workflow.completed', $events);
            $this->assertContains('workflow.straight.completed', $events);
            $this->assertContains('workflow.straight.completed.t1', $events);

            $this->assertContains('workflow.guard', $events);
            $this->assertContains('workflow.straight.guard', $events);
            $this->assertContains('workflow.straight.guard.t2', $events);
        }
    }
}

namespace {

    $events = null;

    function event($ev)
    {
        global $events;
        $events[] = $ev;
    }
}
