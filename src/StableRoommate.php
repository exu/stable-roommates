<?php

/**
 * Stable Roommate Problem Solver
 *
 * @see http://en.wikipedia.org/wiki/Stable_roommates_problem
 * @see http://www.dcs.gla.ac.uk/~pat/jchoco/roommates/papers/Comp_sdarticle.pdf
 */
class StableRoommate
{
    public $preferences = [];
    public $proposed = [];
    public $rejections = [];
    public $proposals = [];

    /**
     * @param array $preferenceList
     */
    public function __construct($preferenceList)
    {
        $this->preferences = $preferenceList;

        foreach ($this->preferences as $key => $value) {
            $this->rejections[$key] = [];
            $this->proposals[$key] = [];
        }
    }

    /**
     * This phase of the algorithm will terminate either
     * (i) with every person holding a proposal (as in the example above), or
     * (ii) with one person rejected by everyone
     *
     * @return bool
     */
    public function isFinished()
    {
        $proposalsCount = count($this->proposals);
        $allHaveProposals = count($this->proposed) === $proposalsCount;

        $personRejectedByAll = false;
        foreach ($this->rejections as $personRejections) {
            if (count($personRejections) === $proposalsCount) {
                $personRejectedByAll = true;
            }
        }


        fwrite(STDERR, print_r($allHaveProposals, 1) . "\n");
        fwrite(STDERR, print_r($personRejectedByAll, 1) . "\n");

        return $allHaveProposals || $personRejectedByAll;
    }


    /**
     * reject proposal
     *
     * @param mixed $person
     * @param mixed $chosen
     *
     * @return bool
     */
    public function reject($person, $chosen)
    {
        $this->rejections[$person][] = $chosen;
        $key = array_search($chosen, $this->preferences[$person]);
        $this->preferences[$person][$key] = null;
    }

    /**
     * reject weakest proposal for person
     *
     * @param mixed $person
     *
     * @return bool
     */
    public function rejectWeakest($person)
    {
        foreach (array_reverse($this->preferences[$person]) as $preference) {
            if (!is_null($preference)) {
                break;
            }
        }

        if ($preference) {
            $this->reject($person, $preference);

            return true;
        }

        return false;
    }


    /**
     * accept proposal
     *
     * @param mixed $person
     * @param mixed $chosen
     *
     * @return bool
     */
    public function accept($person, $chosen)
    {
        $this->proposals[$person][] = $chosen;
        $this->proposed[$chosen] = $person;
        echo "Person $chosen accepting $person\n";
    }

    /**
     * Checks if person has better proposal previosly made by other mate
     *
     * @param mixed $person
     * @param mixed $chosen
     *
     * @return bool
     */
    public function hasBetterProposalThan($person, $chosen)
    {
        $previous = $this->getPersonProposal($chosen);

        if ($previous) {
            // check which one is better if any previos proposal
            $previousRank = array_search($previous, $this->preferences[$chosen]);
            $currentRank  = array_search($person, $this->preferences[$chosen]);
            if ($currentRank === false) {
                // current proposal was rejected before
                return true;
            }
            $hasBetterProposalThan = $previousRank < $currentRank;

            echo "Person $chosen " . ($hasBetterProposalThan ? "has": "has't") . " better proposal than $person ($previousRank < $currentRank) \n";

            if (!$hasBetterProposalThan) {
                $this->reject($person, $previous);
            }

            return $hasBetterProposalThan;
        } else {
            return false;
        }
    }

    /**
     * Choose or reject proposal
     *
     * @param mixed $person
     * @param mixed $chosen
     */
    protected function validate($person, $chosen)
    {
        if (!isset($this->preferences[$person])) {
           throw new \InvalidArgumentException("There is no {$person} preferences");
        }

        if (array_search($chosen, $this->preferences[$person]) === false) {
           throw new \InvalidArgumentException("There is no {$chosen} in {$person} preferences list");
        }
    }

    /**
     * Choose or reject proposal
     *
     * @param mixed $person
     * @param mixed $chosen
     *
     * @return bool
     */
    public function propose($person, $chosen)
    {
        echo "Person $person proposing $chosen\n";
        $this->validate($person, $chosen);

        if ($this->hasBetterProposalThan($person, $chosen)) {
            echo "Person $chosen rejected by $person\n";
            $this->reject($person, $chosen);

            return false;
        } else {
            $this->accept($person, $chosen);

            return true;
        }
    }

    /**
     * Get all proposals
     *
     * @return array
     */
    public function getProposals()
    {
        return $this->preferences;
    }

    /**
     * Get person proposal
     *
     * @param mixed $person
     *
     * @return array
     */
    public function getPersonProposal($person)
    {
        if (isset($this->proposed[$person])) {
            return $this->proposed[$person];
        } else {
            return null;
        }
    }

    public function printProposals()
    {

        echo "\n";
        foreach ($this->getProposals() as $person => $proposals) {
            echo "$person:\t";
            foreach ($proposals as $proposal) {
                echo ($proposal ? $proposal : "❌      ") . "\t\t";
            }
            echo "\n";
        }
    }


    public function proposeUntilSuccess($person, $personProposals, $level = 1)
    {
        if (!isset($personProposals[$level])) {
            return false;
        }

        $accepted = $this->propose($person, $personProposals[$level]);

        if (!$accepted) {
            return $this->proposeUntilSuccess($person, $personProposals, $level+1);
        }
    }


    /**
     * Run run run
     */
    public function runPhase1()
    {
        $proposals = $this->getProposals();

        $level = 0;
        foreach ($proposals as $person => $personProposals) {
            echo "\n";
            if (isset($personProposals[$level])) {
                $this->proposeUntilSuccess($person, $personProposals);
            }

            $this->printProposals();
        }

        fwrite(STDERR, print_r($this->proposed, 1) . "\n");
        echo "<PRE>" . print_r($this->rejections, 1) . "</PRE>";

    }


}
