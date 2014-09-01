describe('Hello World form', function() {

    beforeEach(function() {
        return browser.ignoreSynchronization = true;
    });

    it("user success login", function () {

        browser.get('/wp-login');

        browser.sleep( 2000 );

        element(by.id('user_login')).clear();

        element(by.id('user_login')).sendKeys('maronl_admin');

        element(by.id('user_pass')).clear();

        element(by.id('user_pass')).sendKeys('maronl');

        element(by.id('wp-submit')).click();

        browser.sleep( 2000 );

        expect(element(by.id("login_error")).isPresent()).toBe(false);

    });

    it("add new secure attachment to hello world post", function () {

        browser.get('/wp-admin/edit.php');

        browser.actions().mouseMove(element(by.id('post-1'))).perform();

        browser.sleep( 2000 );

        element(by.css('.edit')).click();

        browser.sleep( 2000 );

        element(by.css('#secure-attachments-file')).sendKeys('C:/xampp/htdocs/wp-plugin-dev/wordpress/wp-content/plugins/secure-attachments/tests/data/sample-file/prova.pdf');

        element(by.css('#secure-attachments-file-title')).sendKeys('prova test e2e');

        element(by.css('#upload-secure-attachments-button')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova.pdf"]')).isPresent()).toBe(true);

        element(by.css('a.sadelbutton[href="prova.pdf"]')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova.pdf"]')).isPresent()).toBe(false);

        browser.sleep( 2000 );

    });

    it("change metadata to secure attachment", function () {

        browser.get('/wp-admin/edit.php');

        browser.actions().mouseMove(element(by.id('post-1'))).perform();

        element(by.css('.edit')).click();

        browser.sleep( 2000 );

        element(by.css('#secure-attachments-file')).sendKeys('C:/xampp/htdocs/wp-plugin-dev/wordpress/wp-content/plugins/secure-attachments/tests/data/sample-file/prova.pdf');

        element(by.css('#secure-attachments-file-title')).sendKeys('prova test e2e');

        element(by.css('#upload-secure-attachments-button')).click();

        browser.sleep( 2000 );

        element(by.css('#secure-attachments-file')).sendKeys('C:/xampp/htdocs/wp-plugin-dev/wordpress/wp-content/plugins/secure-attachments/tests/data/sample-file/prova2.pdf');

        element(by.css('#secure-attachments-file-title')).sendKeys('seconda prova test e2e');

        element(by.css('#upload-secure-attachments-button')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova.pdf"]')).isPresent()).toBe(true);

        expect(element(by.css('a.sadelbutton[href="prova2.pdf"]')).isPresent()).toBe(true);

        browser.sleep( 2000 );

        expect(element(by.css('a[data-action=modify-attached-document][href="prova.pdf"]')).isPresent()).toBe(true);

        element(by.css('a[data-action=modify-attached-document][href="prova.pdf"]')).click();

        browser.sleep( 2000 );

        element(by.css('#secure-attachments-modify-file-title')).clear();

        element(by.css('#secure-attachments-modify-file-title')).sendKeys('titolo modificato');

        browser.sleep( 2000 );

        element(by.css('#modify-secure-attachments-button')).click();

        browser.sleep( 2000 );

        expect(element(by.css('.file-title[data-value="prova.pdf"]')).isPresent()).toBe(true);

        expect(element(by.css('.file-title[data-value="prova.pdf"]')).getText()).toBe("titolo modificato");

    });


    it("deleting test files", function () {

        browser.get('/wp-admin/edit.php');

        browser.actions().mouseMove(element(by.id('post-1'))).perform();

        element(by.css('.edit')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova.pdf"]')).isPresent()).toBe(true);

        element(by.css('a.sadelbutton[href="prova.pdf"]')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova2.pdf"]')).isPresent()).toBe(true);

        element(by.css('a.sadelbutton[href="prova2.pdf"]')).click();

        browser.sleep( 2000 );

        expect(element(by.css('a.sadelbutton[href="prova.pdf"]')).isPresent()).toBe(false);

        expect(element(by.css('a.sadelbutton[href="prova2.pdf"]')).isPresent()).toBe(false);

    });

});