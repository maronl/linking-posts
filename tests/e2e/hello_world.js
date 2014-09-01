describe('Hello World form', function() {

    beforeEach(function() {
        return browser.ignoreSynchronization = true;
    });

    it('display right title', function() {

        browser.get('/');

        expect(browser.getTitle()).toBe("wp plugin development by maronl | Just another WordPress site");

    });

    it("user fail login", function () {

        browser.get('/wp-login');

        element(by.id('user_login')).clear();

        element(by.id('user_login')).sendKeys('tfnico');

        element(by.id('user_pass')).clear();

        element(by.id('user_pass')).sendKeys('tfnico');

        element(by.id('wp-submit')).click();

        browser.sleep( 1000 );

        expect(element(by.id("login_error")).isPresent()).toBe(true);

    });

    it("user success login", function () {

        browser.get('/wp-login');

        element(by.id('user_login')).clear();

        element(by.id('user_login')).sendKeys('maronl_admin');

        element(by.id('user_pass')).clear();

        element(by.id('user_pass')).sendKeys('maronl');

        element(by.id('wp-submit')).click();

        browser.sleep( 1000 );

        expect(element(by.id("login_error")).isPresent()).toBe(false);

    });

});