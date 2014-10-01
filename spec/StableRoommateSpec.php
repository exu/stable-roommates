<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
Unlike the McVitie/Wilson algorithm for the stable marriage problem.
The first phase of the algorithm is based on a sequence of "proposals" made
by one person to another. This sequence of proposals proceeds with each
individual pursuing the following strategies:

I.

If x receives a proposal from y, then

(a) he rejects it at once if he already holds a better proposal (i.e., a
proposal from someone higher than y in his preference list);

(b) he holds it for consideration otherwise, simultaneously rejecting
any poorer proposal that he currently holds.


II.

An individual x proposes to the others in the order in which they
appear in his preference list, stopping when a promise of consideration is
received; any subsequent rejection causes x to continue immediately his
sequence of proposals.

*/

class StableRoommateSpec extends ObjectBehavior
{
    function let() {

        // preference list
        $this->beConstructedWith([
            'jacek'  => ['tomek', 'atomek', 'tytus'],
            'tomek'  => ['jacek', 'tytus', 'atomek'],
            'atomek' => ['tomek', 'tytus', 'jacek'],
            'tytus'  => ['tomek', 'atomek', 'jacek'],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('StableRoommate');
    }

    function it_should_throw_exception_when_proposing_person_out_of_scope()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringPropose("non-existing-person", "non-existing-person");
    }

    function it_should_throw_exception_when_proposed_person_doesnt_exists()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringPropose("jacek", "non-existing-person");
    }

    function it_checks_that_there_is_better_proposal()
    {
        // jacek prefers tomek over atomek
        $this->propose("tomek", "jacek");
        $this->hasBetterProposalThan("atomek", "jacek")->shouldReturn(true);
    }

    function it_checks_that_there_is_no_better_proposal()
    {
        $this->propose("atomek", "jacek");
        $this->hasBetterProposalThan("tomek", "jacek")->shouldReturn(false);
    }

    function it_rejects_element()
    {
        $this->beConstructedWith([
            'jacek'  => ['tomek'],
            'tomek'  => ['jacek'],
        ]);

        $this->reject("jacek", "tomek");

        $this->getProposals()->shouldReturn([
            'jacek'  => [null],
            'tomek'  => ['jacek'],
        ]);
    }

    function it_accept_element()
    {
        $this->beConstructedWith([
            'jacek'  => ['tomek'],
            'tomek'  => ['jacek'],
        ]);

        $this->accept("jacek", "tomek");

        $this->getPersonProposal("tomek")->shouldReturn("jacek");
    }

    function it_rejects_weakest_proposal()
    {
        $this->beConstructedWith([
            'jacek'  => ['tomek', 'atomek'],
            'tomek'  => ['jacek', 'atomek'],
            'atomek'  => ['jacek', 'tomek'],
        ]);

        $this->rejectWeakest("jacek");

        $this->getProposals()->shouldReturn([
            'jacek'  => ['tomek', null],
            'tomek'  => ['jacek', 'atomek'],
            'atomek'  => ['jacek', 'tomek'],
        ]);
    }


    function it_runs()
    {
        $this->beConstructedWith([
            "person1" => ["person3",   "person4",   "person2",   "person6",   "person5"],
            "person2" => ["person6",   "person5",   "person4",   "person1",   "person3"],
            "person3" => ["person2",   "person4",   "person5",   "person1",   "person6"],
            "person4" => ["person5",   "person2",   "person3",   "person6",   "person1"],
            "person5" => ["person3",   "person1",   "person2",   "person4",   "person6"],
            "person6" => ["person5",   "person1",   "person3",   "person4",   "person2"],
        ]);




        $this->runPhase1();

        $this->getProposals()->shouldReturn([
            "person1" => [null,        "person4",   "person2",   "person6",   null,    ],
            "person2" => ["person6",   "person5",   "person4",   "person1",   "person3"],
            "person3" => ["person2",   "person4",   "person5",    null,       "person6"],
            "person4" => ["person5",   "person2",   "person3",   "person6",   "person1"],
            "person5" => ["person3",   null,        "person2",   "person4",   null     ],
            "person6" => [null,        "person1",   null,        "person4",   "person2"],
        ]);
    }
}
