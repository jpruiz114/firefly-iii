<?php
/**
 * ConfirmationControllerTest.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms of the
 * Creative Commons Attribution-ShareAlike 4.0 International License.
 *
 * See the LICENSE file for details.
 */

namespace Auth;

use FireflyIII\Models\Configuration;
use FireflyIII\Models\Preference;
use FireflyIII\Support\Facades\FireflyConfig;
use FireflyIII\Support\Facades\Preferences;
use TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-12-07 at 18:50:31.
 */
class ConfirmationControllerTest extends TestCase
{


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @covers \FireflyIII\Http\Controllers\Auth\ConfirmationController::confirmationError
     */
    public function testConfirmationError()
    {
        // need a user that is not activated. And site must require activated users.
        $trueConfig            = new Configuration;
        $trueConfig->data      = true;
        $falsePreference       = new Preference;
        $falsePreference->data = false;

        Preferences::shouldReceive('get')->withArgs(['user_confirmed', false])->andReturn($falsePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthEnabled', false])->andReturn($falsePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthSecret'])->andReturn(null);

        FireflyConfig::shouldReceive('get')->withArgs(['must_confirm_account', false])->andReturn($trueConfig);
        $this->be($this->user());
        $this->call('GET', route('confirmation_error'));
        $this->assertResponseStatus(200);
        $this->see('has been sent to the address you used during your registration');

    }

    /**
     * @covers \FireflyIII\Http\Controllers\Auth\ConfirmationController::doConfirmation
     */
    public function testDoConfirmation()
    {
        $codePreference        = new Preference;
        $codePreference->data  = 'abcde';
        $timePreference        = new Preference;
        $timePreference->data  = 0;
        $falsePreference       = new Preference;
        $falsePreference->data = false;

        Preferences::shouldReceive('get')->withArgs(['user_confirmed_code'])->andReturn($codePreference);
        Preferences::shouldReceive('get')->withArgs(['user_confirmed_last_mail', 0])->andReturn($timePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthEnabled', false])->andReturn($falsePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthSecret'])->andReturn(null);
        Preferences::shouldReceive('get')->withArgs(['user_confirmed', false])->andReturn($falsePreference);

        $this->be($this->user());
        $this->call('GET', route('do_confirm_account', ['abcde']));
        $this->assertResponseStatus(302);
        $this->assertRedirectedToRoute('home');
    }

    /**
     * @covers \FireflyIII\Http\Controllers\Auth\ConfirmationController::resendConfirmation
     */
    public function testResendConfirmation()
    {
        $trueConfig            = new Configuration;
        $trueConfig->data      = true;
        $codePreference        = new Preference;
        $codePreference->data  = 'abcde';
        $timePreference        = new Preference;
        $timePreference->data  = 0;
        $falsePreference       = new Preference;
        $falsePreference->data = false;

        Preferences::shouldReceive('get')->withArgs(['user_confirmed_last_mail', 0])->andReturn($timePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthEnabled', false])->andReturn($falsePreference);
        Preferences::shouldReceive('get')->withArgs(['twoFactorAuthSecret'])->andReturn(null);
        FireflyConfig::shouldReceive('get')->withArgs(['must_confirm_account', false])->andReturn($trueConfig);
        Preferences::shouldReceive('get')->withArgs(['user_confirmed', false])->andReturn($falsePreference);

        // from event handler:
        Preferences::shouldReceive('setForUser')->withAnyArgs();

        $this->be($this->user());
        $this->call('GET', route('resend_confirmation'));
        $this->assertResponseStatus(200);
    }

}