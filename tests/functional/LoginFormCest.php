<?php

namespace tests\functional;
use models;

class LoginFormCest
{
    public function _before(\tests\_support\FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(\tests\_support\FunctionalTester $I)
    {
        $I->see('Login', 'h1');

    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginById(\tests\_support\FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginByInstance(\tests\_support\FunctionalTester $I)
    {
        $I->amLoggedInAs(models\User::findByUsername('admin'));
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    public function loginWithEmptyCredentials(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    public function loginSuccessfully(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin',
        ]);
        $I->see('Logout (admin)');
        $I->dontSeeElement('form#login-form');
    }
}