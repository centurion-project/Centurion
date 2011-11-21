<?php

class Selenium_BackOffice extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp()
    {
      $this->setBrowser("*chrome");
      $this->setBrowserUrl(Centurion_Config_Manager::get('test.serverurl'));
    }

    public function loginTestCase()
    {
        $this->open("/admin/login?next=/admin/");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->type("password", "mot de passe pas ok");
        $this->click("_login");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->type("password", "mot de passe pas ok");
        $this->click("remember_me");
        $this->click("_login");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
    }
    
    public function naviguerTestCase()
    {
         $this->open("/cms/admin-flatpage/index/language/fr");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("menu-3");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("menu-4");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("//section/nav/ul/li[1]/a");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("//section/nav/ul/li[2]/a");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("menu-9");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("menu-2");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("//nav/ul/li[3]/a");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("//section/nav/ul/li[2]/a");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("//section/nav/ul/li[1]/a");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->click("menu-4");
        $this->waitForPageToLoad("30000");
        $this->assertEquals("Centurion backoffice", $this->getTitle());
        $this->assertEquals("Centurion backoffice", $this->getTitle());
    }
}
