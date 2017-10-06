<?php 

namespace Karolina\Experiment;

use Respect\Validation\Validator as v;
use \Karolina\User\User;

class Experiment
{
    private $languageFields;
    use \Karolina\Language\LanguageTrait;
    private $customLanguageFields;
    use \Karolina\Language\CustomLanguageTrait;
    private $team;
    use \Karolina\Team\TeamTrait;
    private $images = array();
    use \Karolina\Image\ImageCollectionTrait;
    private $settings;
    use \Karolina\Setting\SettingTrait;

    private $id;
    private $stage = 0;
    private $disabled = false;
    private $placesToShow = null;
    private $tags = array();
    private $fundingSources;
    private $fundingGoalAmount;
    private $fundingGoalCurrency;
    private $network;
    private $links = array();

    private $questions = array();


    public function __construct()
    {
        $this->languageFields = new \Karolina\Language\LanguageFields();
        $this->customLanguageFields = new \Karolina\Language\LanguageFields();
        $this->team = new \Karolina\Team\Team();
        $this->fundingSources = new FundingSources();
        $this->fundingGoalCurrency = new \Karolina\Currency('EUR');
        $this->network = new \Karolina\Network\Root(
            new \Karolina\Network\Node('experiments')
            );
        $this->setSettingsGroup(new \Karolina\Setting\SettingsGroup());
    }

    public function moveToNextStage()
    {
        if ($this->stage == 5) {
            throw new \Karolina\Exception('You are already at the last stage. Your stage is: '.$this->getStage());
        }
        $nextStage = $this->stage + 1;

        // Current long description
        $orgLongDescField = $this->stage.'_long_description';
        $nextLongDescField = $nextStage.'_long_description';

        if ($this->getCustomLanguage($nextLongDescField) == "") {
            $this->customLanguageFields->duplicateFields($orgLongDescField, $nextLongDescField);
        }

        $this->setStage($nextStage);
    }

    public function setGeographicLocation($location)
    {
        $location = (string) $location;
        $this->setSetting('geographic_location', $location);
    }

    public function getGeographicLocation()
    {
        if ($this->settingIsSet('geographic_location')) {
            return $this->setting('geographic_location');
        } else {
            return "";
        }
    }

    public function setLinks(array $links)
    {
        $newLinks = array();
        foreach ($links as $link) {
            $newLinks[] = new \Karolina\Content\Link($link['url'], $link['title']);
        }

        $this->links = $newLinks;
    }

    public function getLinks()
    {
        $linkDocs = array();
        foreach ($this->links as $link) {
            $doc = new \Karolina\Content\LinkDocument($link);
            $linkDocs[] = $doc->get();
        }
        return $linkDocs;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }


    public function getStage()
    {
        return $this->stage;
    }

    public function setStage($stage)
    {
        $this->stage = (int) $stage;
    }

    /*
        Likes and networking

    */

    public function getNetworkType()
    {
        return "experiments";
    }

    public function setNetwork(\Karolina\Network\Root $root)
    {
        $this->network = $root;
    }

    public function getNetwork()
    {
        return $this->network;
    }

    public function addLike($liker)
    {
        $this->network->addFollower($liker, "like");
    }


    public function removeLike($liker)
    {
        $this->network->removeFollower($liker, "like");
    }

    public function countLikes()
    {
        return $this->network->countFollowersByRelationship('like');
    }

    public function getLikers()
    {
        return $this->network->getFollowerIdsByRelationship('like');
    }


    public function getUsersWhoLike()
    {
        $followers = $this->network->getFollowersByRelationship('like');

        $users = array();

        foreach ($followers as $relationship) {
            $users[] = $relationship->getFollower();
        }

        return $users;
    }



    /*
        FUNDING
    */

    public function getFundingDocument()
    {
        $funding['sources'] = $this->fundingSources->getDocument();
        $funding['goal'] = $this->fundingGoalAmount;
        $funding['currency'] = $this->fundingGoalCurrency->getISO();

        return $funding;
    }

    public function setFundingGoalAmount($amount)
    {
        if (!v::intVal()->not(v::negative())->validate($amount)) {
            throw new \Karolina\Exception("Amount for funding goal must be a non-negative natural number. Amount not allowed: ".htmlentities($amount));
        }

        $this->fundingGoalAmount = (int) $amount;
    }

    public function setFundingGoalCurrency($currencyKey)
    {
        $this->fundingGoalCurrency = new \Karolina\Currency($currencyKey);
    }


    public function setFundingGoal($amount, $currencyKey = "EUR")
    {
        if (!v::intVal()->not(v::negative())->validate($amount)) {
            throw new \Karolina\Exception("Amount for funding goal must be a non-negative natural number. Amount not allowed: ".htmlentities($amount));
        }

        $this->fundingGoalAmount = $amount;
        $this->fundingGoalCurrency = new \Karolina\Currency($currencyKey);
    }

    public function setStateFunding($amount)
    {
        return $this->fundingSources->setFundingSource('state', $amount);
    }

    public function setOrganizationsFunding($amount)
    {
        return $this->fundingSources->setFundingSource('organizations', $amount);
    }

    public function setCrowdFunding($apiURL, $campaignId)
    {
        return $this->fundingSources->setExternalFundingSource('crowd', $apiURL, $campaignId);
    }

    public function setFundingFromDocument($doc)
    {
        if (isset($doc['goal'])) {
            $this->setFundingGoal($doc['goal'], $doc['currency']);
        }

        if (isset($doc['sounces'])) {
            $this->fundingSources->setFromDocument($doc['sources']);
        }

        $this->fundingSources->setFromDocument($doc['sources']);
    }


    /*
        Tags
    */

    public function replaceTags($tags)
    {
        $this->tags = array();

        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function addTag(\Karolina\Tag\Tag $tag)
    {
        $this->tags[$tag->getId()] = $tag;
    }

    public function removeTag($tagId)
    {
        unset($this->tags[$tagId]);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsDocument()
    {
        $doc = array();
        foreach ($this->tags as $tag) {
            $doc[$tag->getId()] = $tag->getLabelDocument();
        }

        return $doc;
    }

    /*
        Questions and answers
    */


    public function getQuestions()
    {
        return $this->questions;
    }

    public function getQuestionsDocument()
    {
        $doc = array();

        if ($this->getQuestions()) {
            foreach ($this->getQuestions() as $question) {
                $answerDoc = $question->getAnswerDocument();
                $questionDoc = $question->getDocument();

                $doc[$question->getContext()][$question->getId()]['question'] = $questionDoc;
                $doc[$question->getContext()][$question->getId()]['answer'] = $answerDoc;
            }
        }
        return $doc;
    }

    public function addQuestion(\Karolina\Question\Question $question)
    {
        $question->setObjectType('experiment');
        $question->setObjectId($this->getId());
        $this->questions[$question->getContext()] = $question;
    }

    public function getQuestion($context)
    {
        return $this->questions[$context];
    }


    /*
       Language

    */


    public function getLanguageFields()
    {
        $this->languageFields->setObjectId($this->getId());
        return $this->languageFields;
    }

    public function getCustomLanguageFields()
    {
        $this->customLanguageFields->setObjectId($this->getId());
        return $this->customLanguageFields;
    }


    /*
        Where to show (control where in layout or pages experiment is shown)
    */

    public function getPlacesToShow()
    {
        if ($this->placesToShow == null) {
            return new \stdClass();
        } else {
            return $this->placesToShow;
        }
    }

    public function setPlaceToShow($placeName, $set)
    {
        if (!v::alnum('-_')->noWhitespace()->validate($placeName)) {
            throw new \Exception('Place field can only have alpha numeric and _ -');
        }
        if (!v::boolType()->validate($set)) {
            throw new \Exception('Setting for place to show must be true or false.');
        }

        $this->placesToShow[$placeName] = $set;
    }

    /*
        ACCESS CONTROL:
        Outside clients need to enforce access control by checking the below methods.
    */


    public function addCreator(\Karolina\User\User $user)
    {
        $this->addToTeam($user, ['creator']);
    }

    private function isInTeamOrIsAdmin($user)
    {
        return $this->team->isInTeam($user) or $user->isAdmin();
    }

    public function canEditWhereToShow(User $user)
    {
        return $user->isAdmin();
    }
    
    public function canEditAbility(User $user)
    {
        return $user->isAdmin();
    }

    public function canEditSettings(User $user)
    {
        return $this->isInTeamOrIsAdmin($user);
    }

    public function canEditFunding(User $user)
    {
        return $user->isAdmin();
    }


    public function canEditContent(User $user)
    {
        return $this->isInTeamOrIsAdmin($user);
    }


    /*
        ENABLED / DISABLED:
        Disable is like soft delete. Hidden but stil exisgts in DB.

    */

    public function disable()
    {
        $this->disabled = true;
    }


    public function isEnabled()
    {
        if ($this->disabled) {
            return false;
        } else {
            return true;
        }
    }

    public function enable()
    {
        $this->disabled = false;
    }
}
