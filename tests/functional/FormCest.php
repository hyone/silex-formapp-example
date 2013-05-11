<?php

use Codeception\Util\Stub;

class FormCest
{
    private $formName = 'form1_main';

    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    public function straightNormalFlow(\TestGuy $I)
    {
        $input = array(
            'name'  => 'Miles',
            'mail'  => 'test@example.com',
            'year'  => 2011,
            'month' => 5,
            'day'   => 3,
            'sex'   => array('label' => 'female', 'value' => 2)
        );

        # top 
        $I->amOnPage('/');
        $I->fillField('name', $input['name']);
        $I->fillField('mail', $input['mail']);
        $I->selectOption("form select[name=\"{$this->formName}[born][year]\"]", $input['year']);
        $I->selectOption("form select[name=\"{$this->formName}[born][month]\"]", $input['month']);
        $I->selectOption("form select[name=\"{$this->formName}[born][day]\"]", $input['day']);
        $I->selectOption("form  input[name=\"{$this->formName}[sex]\"]", $input['sex']['value']);
        $I->click('submit');

        # confirmation
        $I->seeInCurrentUrl("confirmation");
        $I->seeResponseCodeIs(200);
        $I->see($input['name'], '//ul/li');
        $I->see($input['mail'], '//ul/li');
        $I->see(sprintf(
            "%d-%02d-%02d",
            $input['year'], $input['month'], $input['day']
        ), '//ul/li');
        $I->see($input['sex']['label'], '//ul/li');
        $I->click('submit');

        # success
        $I->seeInCurrentUrl("success");
        $I->seeResponseCodeIs(200);
        $I->see('Thanks to your application!');
    }
}
