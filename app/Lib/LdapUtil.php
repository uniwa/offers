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
        $ldapsrv = Configure::read('Ldap');
        $this->ldapServer  = $ldapsrv['server'];
        $this->ldapPort = $ldapsrv['port'];
        $this->suffix = $ldapsrv['suffix'];
        $this->baseDN = $ldapsrv['baseDN'];
        $this->ldapUser = $ldapsrv['user'];
        $this->ldapPassword = $ldapsrv['password'];

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

    public function exists( $username ){
        // check if username exists in ldap
        ldap_bind( $this->ldap, $this->ldapUser, $this->ldapPassword );

        //$attributes = array( 'givenname;lang-el', 'sn;lang-el', 'cn;lang-el', 'mail' /*, 'memmberof'*/ );
        $attributes = array('uid');
        $filter = "(uid=$username)";

        $result = ldap_search( $this->ldap, $this->baseDN, $filter, $attributes );
        $entries = ldap_get_entries( $this->ldap, $result );

        return $entries['count'] > 0;
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

