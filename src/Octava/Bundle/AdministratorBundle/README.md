# Octava Administrator Bundle

## Installation

```
composer require octava/cms
```

```
//file app/AppKernel.php

//...

    new FOS\UserBundle\FOSUserBundle(),
    new Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),
    new Octava\Bundle\AdministratorBundle\OctavaAdministratorBundle(),
    
//...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        //...
    }
    
```

```
//config.yml
fos_user:
    db_driver:      orm
    firewall_name:  admin
    
security:

    providers:
        fos_userbundle:
            entity: { class: Octava\Bundle\AdministratorBundle\Entity\Administrator }

    role_hierarchy:
        ROLE_ADMIN:       [ROLE_USER, ROLE_SONATA_ADMIN]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH, SONATA]
        
    firewalls:    
        admin:
            pattern:            /%admin.prefix%(.*)
            context:            user
            form_login:
                provider:       fos_userbundle
                login_path:     'sonata_user_admin_security_login'
                use_forward:    false
                check_path:     'sonata_user_admin_security_check'
                failure_path:   null
                default_target_path: 'sonata_admin_dashboard'
                require_previous_session: false
            logout:
                path:           'sonata_user_admin_security_logout'
                target:         'sonata_user_admin_security_login'
            anonymous:          true
            remember_me:
                key:      "remember_me_%secret%"
                lifetime: 86400 # 24 hours in seconds
                path:     /
                domain:   ~ # Defaults to the current domain from $_SERVER

    access_control:
        # Admin login page needs to be access without credential
        - { path: '^/%admin.prefix%/([a-z]{2}/)?login/?$', role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: '^/%admin.prefix%/([a-z]{2}/)?logout/?$', role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: '^/%admin.prefix%/([a-z]{2}/)?login_check/?$', role: IS_AUTHENTICATED_ANONYMOUSLY }

        # Secured part of the site
        - { path: ^/%admin.prefix%/, role: [ROLE_ADMIN, ROLE_SONATA_ADMIN] }
        - { path: ^/.*, role: IS_AUTHENTICATED_ANONYMOUSLY }

```

```
app/console doctrine:fixtures:load
app/console octava:administrator:import-acl-resources
```
