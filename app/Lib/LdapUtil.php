<?php
class LdapUtil {

    private $ldap  = null;//ldap connection


    /*LDAP credentials for connection*/
    private $ldapServer  = null;
    private $ldapPort = null;
    public $suffix = null;
    public $baseDN = null;
    private $ldapUser = null;
    private $ldapPassword = null;

    public function __construct() {

        //loads ldap file from app/config/
        Configure::load( 'ldap' );
        
        $this->ldapServer  = Configure::read('Ldap.server');
        $this->ldapPort = Configure::read( 'Ldap.port' ); 
        $this->suffix = Configure::read( 'Ldap.suffix' ); 
        $this->baseDN = Configure::read( 'Ldap.baseDN' );
        $this->ldapUser = Configure::read( 'Ldap.user'); 
        $this->ldapPassword = Configure::read( 'Ldap.password' );

        /*Connect to LDAP*/
        if ($this->ldapPort == '') {
            $this->ldap =  ldap_connect( $this->ldapServer )
                or die("unable to connect to ldap");
        } else {
            $this->ldap =  ldap_connect( $this->ldapServer, $this->ldapPort )
                or die("unable to connect to ldap");
        }

        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);
    }

    /* Return's true if ldap dir exists for this username and password*/
    public function auth( $user, $pass ) {

        if ( empty( $user ) or empty( $pass ) ) {

            return false;
        }

        /**
         * Bind ldap directory with user's credentials
         * if user has not ldap acount returns false
         **/
        @$good = ldap_bind( $this->ldap, 'uid='.$user.','.$this->baseDN, $pass );
        if( $good === true ) {

            return true;

        } else {

            return false;
        }

    }

    //This function checks for uid existance
    //Returns true if uid already exists
    public function uidCheck( $uid ){

       
        ldap_bind( $this->ldap, $this->ldapUser, $this->ldapPassword );
        /*
         * compares with uid users distinguished name. If user does not exist returns 
         * -1 which is error. Avoiding warnings with @ and accept -1 as false 
         * value
         * */
        @$result = ldap_compare( $this->ldap, 'uid='.$uid.', ou=people,'.$this->baseDN, 'uid', $uid );
        return ($result == 1)?true:false;

    }

    public function __destruct() {

        ldap_unbind( $this->ldap );
    }

    /**
     * Get formated entry from ldap sub-tree with RDN the principal name
     * */
    public function getInfo( $user ) {

        $username =  $user;
        $attributes = array( 'givenname;lang-el', 'sn;lang-el', 'cn;lang-el', 'mail' /*, 'memmberof'*/ );
        $filter = "(uid=$username)";
    
        ldap_bind( $this->ldap, $this->ldapUser, $this->ldapPassword );
        $result = ldap_search( $this->ldap, $this->baseDN, $filter, $attributes );
        $entries = ldap_get_entries( $this->ldap, $result );
        $entries[0]['username'][0] = $username;
        return $this->formatInfo( $entries );
    }

    private function formatInfo( $entries ) {

        $info = array();

        $info['username'] = $entries[0]['username'][0];
        $info['first_name'] =  $entries[0]['givenname;lang-el'][0];
        $info['last_name'] = $entries[0]['sn;lang-el'][0];
        $info['name'] = $entries[0]['cn;lang-el'][0];
        $info['email'] = $entries[0]['mail'][0];
        //$info['groups'] = $this->groups($array[0]['memberof']); for future use
                         
        
        return $info;
    }

    private function groups( $members ) {
       $groups = array();
       $tmp = array();

       /* Creates a temp array with goups info in each record*/
       foreach( $members as $entry ) {

           $tmp = array_merge( $tmp, explode( ',', $entry ) ) ; 
       }

       /*parse records*/
       foreach( $tmp as $value ) {

           if( substr( $value, 0, 2 ) == 'CN' ) {

               $groups[] = substr( $value, 3);
           }
       }

       return $groups;
    }
}

