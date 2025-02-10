<?php

namespace tests\functional;
class ContactFormCest
{
    public function _before(\tests\_support\FunctionalTester $I)
    {
        $I->amOnRoute('site/contact');
    }

    public function openContactPage(\tests\_support\FunctionalTester $I)
    {
        $I->see('Contact', 'h1');
    }

    public function submitEmptyForm(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#contact-form', []);
        $I->expectTo('see validations errors');
        $I->see('Contact', 'h1');
        $I->see('Name cannot be blank');
        $I->see('Email cannot be blank');
        $I->see('Subject cannot be blank');
        $I->see('Body cannot be blank');
        $I->see('The verification code is incorrect');
    }

    public function submitFormWithIncorrectEmail(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#contact-form', [
            'ContactForm[name]' => 'tester',
            'ContactForm[email]' => 'tester.email',
            'ContactForm[subject]' => 'test subject',
            'ContactForm[body]' => 'test content',
            'ContactForm[verifyCode]' => 'testme',
        ]);
        $I->expectTo('see that email address is wrong');
        $I->dontSee('Name cannot be blank', '.help-inline');
        $I->see('Email is not a valid email address.');
        $I->dontSee('Subject cannot be blank', '.help-inline');
        $I->dontSee('Body cannot be blank', '.help-inline');
        $I->dontSee('The verification code is incorrect', '.help-inline');
    }

    public function submitFormSuccessfully(\tests\_support\FunctionalTester $I)
    {
        $I->submitForm('#contact-form', [
            'ContactForm[name]' => 'tester',
            'ContactForm[email]' => 'tester@example.com',
            'ContactForm[subject]' => 'test subject',
            'ContactForm[body]' => 'test content',
            'ContactForm[verifyCode]' => 'testme',
        ]);
        $I->seeEmailIsSent();
        $I->dontSeeElement('#contact-form');
        $I->see('Thank you for contacting us. We will respond to you as soon as possible.');
    }
}
