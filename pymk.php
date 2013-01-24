<?php

require_once 'lib/DataSource.php';
require_once 'lib/tools.php';
require_once 'config.php';

class	PeopleYouMightKnow {
  
    private $iIdUser;
    private $aUsers = array();
    private $aFriends = array();
    private $aTags = array();
    private $aSeens = array();
    private $aKnows = array();
    
    public function	__construct($id_user) {
      $this->iIdUser = $id_user;
    }
    
    public function	find() {
      $this->findCommonFriends();
      $this->findWith('location');
      $this->findWith('school');
      $this->findWith('work');
      $this->findWith('birthdate');
      $this->findWithTag();
      $this->findWithSeen();
      $this->unsetFriends();
      $this->setScore();
    }

    /*
     * We set the rules defining why people might know each other
     * Higher is the score, higher people may know each other
     */
    private function setScore() {
      foreach ($this->aKnows as &$aKnow) {
        $score = 0;

        #Simples
        if ($aKnow['school'])
          $score += WEIGHT_SCHOOL;
        if ($aKnow['location'])
          $score += WEIGHT_LOCATION;
        if ($aKnow['work'])
          $score += WEIGHT_WORK;
        if ($aKnow['seen'])
          $score += ($aKnow['seen'] * WEIGHT_SEEN);
        if ($aKnow['tags'])
          $score += ($aKnow['tags'] * WEIGHT_TAG);

        #Combination School: 
        if ($aKnow['school'] && $aKnow['work'])
          $score += WEIGHT_SCHOOL_WORK;
        if ($aKnow['school'] && $aKnow['location'])
          $score += WEIGHT_SCHOOL_WORK;
        if ($aKnow['school'] && $aKnow['location'] && $aKnow['work'])
          $score += WEIGHT_SCHOOL_LOCATION_WORK;
        if ($aKnow['school'] && $aKnow['birthdate'])
          $score += WEIGHT_SCHOOL_BIRTH;
        
        #Combination Location: 
        if ($aKnow['location'] && $aKnow['birthdate'])
          $score += WEIGHT_LOCATION_BIRTH;
        if ($aKnow['location'] && $aKnow['work'])
          $score += WEIGHT_LOCATION_WORK;
        if ($aKnow['location'] && $aKnow['seen'])
          $score += WEIGHT_LOCATION_SEEN;
   
        #Combination Common Friends: 
        if ($aKnow['commons'])
          $score += WEIGHT_COMMON * $aKnow['commons'];
        if ($aKnow['commons'] && $aKnow['school'])
          $score += WEIGHT_COMMON_SCHOOL;
        if ($aKnow['commons'] && $aKnow['work'])
          $score += WEIGHT_COMMON_WORK;
        if ($aKnow['commons'] && $aKnow['location'])
          $score += WEIGHT_COMMON_LOCATION;
        
        $aKnow['score'] = $score;
      }
      array_sort_by_column($this->aKnows, 'score');      
    }
    
    /*
     * Get Level2 people (common friends)
     */    
    private function findCommonFriends() {
        
      foreach ($this->aUsers as $aUser) {
        # If one of my friend is his friend
        foreach ($this->aUsers[$this->iIdUser]['friends'] as $iIdFriend) {
           if (isset($this->aUsers[$iIdFriend]['friends'][$aUser['id']]))
            $this->updateCriteria($aUser['id'], 'commons', 1);
        }
      }
    }
        
    /*
     * Get commons links with users
     * @param $criteria: the criteria in $this->users you want match
     */    
    private function findWith($criteria) {
      $sMyCriteria = $this->aUsers[$this->iIdUser][$criteria];
      foreach ($this->aUsers as $aUser) {
        if ($criteria == 'birthdate' && (abs($aUser['birthdate'] - $sMyCriteria) < MAX_AGE)   ) {
          $this->updateCriteria($aUser['id'], $criteria, true);          
        }        
        elseif ($aUser[$criteria] == $sMyCriteria)
          $this->updateCriteria($aUser['id'], $criteria, true);
      }
    }
    
    /*
     * Get users beeing tagged with you in pictures
     */
    private function findWithTag() {
      foreach ($this->aTags as $aTag) {
        $aIdUsers = explode(',', $aTag['id_users']);
        if (in_array($this->iIdUser, $aIdUsers)) {
          foreach ($aIdUsers as $iId)
            $this->updateCriteria($iId, 'tags', true);
        }
                
      }
    }
    
    /*
     * Get users "spying" your profile
     */
    private function findWithSeen() {
      
      foreach ($this->aSeens as $aSeen) {
        if ($this->iIdUser == $aSeen['id_seen'])
            $this->updateCriteria($aSeen['id_viewer'], 'seen', $aSeen['times']);
          
      }
    } 

    /*
     * Update $this->aKnows, where we store people we might know
     * @param $iIdUser: id user we might know
     * @param $sCriteria: the type of criteria
     * @param $bValue: the value to inc
     */
    private function updateCriteria($iIdUser, $sCriteria, $bValue = false) {      
      if (isset ($this->aKnows[$iIdUser])) {
        if ($sCriteria == 'commons' || $sCriteria == 'tags') {
          $bValue += $this->aKnows[$iIdUser][$sCriteria];
        }
        $this->aKnows[$iIdUser][$sCriteria] = $bValue;        
      }
      else {
        $this->aKnows[$iIdUser] = array(  "commons" =>    0,
                                          "location" =>   false,
                                          "work" =>       false,
                                          "school" =>     false,
                                          "tags" =>       0,
                                          "birthdate" =>  false,
                                          "score" =>      0,
                                          "seen" =>       false);        
        $this->aKnows[$iIdUser][$sCriteria] = $bValue;
      }
    }

    public function show($limit = 5) {
      
      $name = $this->aUsers[$this->iIdUser]['firstname'] . ' ' . $this->aUsers[$this->iIdUser]['lastname'] ;
      echo "\nPeople $name Might Know:\n\n";

      $i = 0;
      foreach ($this->aKnows as $aKnow) {
        if ($i >= $limit)
          break;
        
        $id = $aKnow['id'];
        echo $this->aUsers[$id]['firstname'] . ' '.$this->aUsers[$id]['lastname']."\n";
        echo "Score: ". $aKnow['score']. "\n";
        echo "Commons friends: ". $aKnow['commons']. "\n";
        if ($aKnow['work'])
          echo "Same society: ". $this->aUsers[$id]['work']. "\n";
        if ($aKnow['location'])
          echo "Same location: ". $this->aUsers[$id]['location']. "\n";
        if ($aKnow['school'])
          echo "Same School: ". $this->aUsers[$id]['school']. "\n";
        if ($aKnow['tags'])
          echo "Tagged together: ". $aKnow['tags']. "\n";
        if ($aKnow['birthdate'])
          echo "Same rank age\n";
        echo "\n\n";
        $i++;
      }
    }


    public function	loadUsers($sPath) {
      $oCsv = $this->loadCSV($sPath);
      $aTmpUsers = $oCsv->connect();
      
      foreach ($aTmpUsers as $value) {
        #Optimisation: put the value as key
        $this->aUsers[$value['id']] = $value;
      }
      return $this->aUsers;
    }
    
    public function	loadFriends($sPath) {
      $oCsv = $this->loadCSV($sPath);
      $aTmpFriends = $oCsv->connect();

      #Create friends list
      foreach ($aTmpFriends as $value) {
        $this->aFriends[$value['id_friend_left']][$value['id_friend_right']] = $value['id_friend_right'];
        $this->aFriends[$value['id_friend_right']][$value['id_friend_left']] = $value['id_friend_left'];               
      }
      
      #assign friendships to $this->aUsers
      foreach ($this->aUsers as &$aUser) {
        $aTmpUser[$aUser['id']] = $aUser;
        $aUser['friends'] = isset($this->aFriends[$aUser['id']]) ? $this->aFriends[$aUser['id']] : false;
      }      
      return $this->aFriends;
    }
    
    public function	loadSeens($sPath) {
      $oCsv = $this->loadCSV($sPath);
      $this->aSeens = $oCsv->connect();
      return $this->aSeens;
    }

    public function	loadTags($sPath) {
      $oCsv = $this->loadCSV($sPath);
      $this->aTags = $oCsv->connect();
      return $this->aTags;
    }
    
    /*
     * Load CSV
     */
    private function  loadCSV($sPath) {
		$oCsv = new File_CSV_DataSource;
		$aSettings = array(
			'delimiter' => ';',
			'eol' => "\n",
			'escape' => '"'
		);
		$oCsv->settings($aSettings);
		$oCsv->load($sPath);
        return $oCsv;
    }
	
    /*
     * Don't include our friends
     */
    private function unsetFriends() {
      
      foreach ($this->aKnows as $i => $aKnow) {
        $this->aKnows[$i]['id'] = $i;
      }      
      foreach ($this->aUsers[$this->iIdUser]['friends'] as $iFriends) {
        if (isset($this->aKnows[$iFriends]))
          unset ($this->aKnows[$iFriends]);
      }
    }

  }
  
?>