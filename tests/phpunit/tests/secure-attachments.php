<?php

class Tests_Secure_Attachments extends WP_UnitTestCase {
    function setUp() {
        parent::setUp();
        $secureAttachmentsOptions = array(
            'upload-dir'       => 'C:/xampp/htdocs/wp-plugin-dev/tests/plugin-tests/tests/phpunit/data/working-directory-test',
            'max-file-size'    => '1',
            'file-extension' => 'pdf,jpg,jpeg,png',
            'file-type'  => 'application/pdf,image/jpeg,image/jpg,image/pjpeg,image/x-png,image/png'
        );
        add_option( 'secure-attachments-options', $secureAttachmentsOptions );
    }

    function tearDown() {
        parent::tearDown();
        delete_option( 'secure-attachments-options' );
    }

    function test_default_plugin_options_values() {
        $secureAttachmentOptions = get_option( 'secure-attachments-options' );
        $this->assertCount( 4, $secureAttachmentOptions, 'le opzioni indicate nel record secure-attachments-options devono essere 4' );
        $this->assertEquals( 'C:/xampp/htdocs/wp-plugin-dev/tests/plugin-tests/tests/phpunit/data/working-directory-test', $secureAttachmentOptions['upload-dir']);
        $this->assertEquals( '1', $secureAttachmentOptions['max-file-size']);
        $this->assertEquals( 'pdf,jpg,jpeg,png', $secureAttachmentOptions['file-extension']);
        $this->assertEquals( 'application/pdf,image/jpeg,image/jpg,image/pjpeg,image/x-png,image/png', $secureAttachmentOptions['file-type']);
    }

    function test_secure_attachments_class_inizialization() {
        $secure_attachments = new Secure_Attachments();
        $this->assertInstanceOf('Secure_Attachments', $secure_attachments);
    }

    function test_secure_attachments_validation_methods() {
        $secure_attachments = new Secure_Attachments();
        $this->assertTrue($secure_attachments->isValidFileSize(100));
        $this->assertTrue($secure_attachments->isValidFileExtension('pdf'));
        $this->assertTrue($secure_attachments->isValidFileExtension('png'));
        $this->assertFalse($secure_attachments->isValidFileExtension('txt'));
        $this->assertTrue($secure_attachments->isValidFileType('image/jpeg'));
        $this->assertTrue($secure_attachments->isValidFileType('image/png'));
        $this->assertFalse($secure_attachments->isValidFileType('text/plain'));
    }

    /**
     * @dataProvider sampleUploadedFileOK
     */
    public function test_validation_uploaded_file( $file_title, $fake_uploaded_file ) {
        $secure_attachments = new Secure_Attachments();
        $this->assertTrue( $secure_attachments->validateUploadedFile( $fake_uploaded_file, $file_title ) );
        $this->assertCount( 0, $secure_attachments->getValidationErrors(), 'Un file valido dovrebbe generare un array di errori di validazoni vuoto');
    }

    /**
     * @dataProvider sampleUploadedFileError
     */
    public function test_validation_uploaded_file_error( $file_title, $fake_uploaded_file, $error_message_index ) {
        $secure_attachments = new Secure_Attachments();
        $this->assertFalse( $secure_attachments->validateUploadedFile( $fake_uploaded_file, $file_title ), 'un file non validato perchè con errore durenta l\'upload ha passato il test di validazione' );
        $this->assertArrayHasKey( $error_message_index, $secure_attachments->getValidationErrors() );
    }

    /**
     * @dataProvider sampleUploadedFileOK
     */
    public function test_save_uploaded_file( $file_title, $fake_uploaded_file, $fake_params ) {
        $post_id = $this->factory->post->create();

        $secure_attachments = $this->secure_attachment_mock( $post_id );

        $this->assertCount( 0, $secure_attachments->getAttachments(), 'gli allegati del post devo essere inizialmente 0' );
        $this->assertTrue( $secure_attachments->saveUploadedFile( $fake_uploaded_file, $fake_params ), 'un file valido non è stato salvato?' );
        $this->assertCount( 1, $secure_attachments->getAttachments(), 'gli allegati del post devo essere alla fine del test 1' );

    }

    public function test_order_saving_uploaded_file() {

        $post_id = $this->factory->post->create( array( 'post_status' => 'publish' ) );

        $secure_attachments = $this->secure_attachment_mock( $post_id );

        $fileToBeUploaded = $this->sampleUploadedFileSequenceOK();

        $this->assertCount( 0, $secure_attachments->getAttachments(), 'gli allegati del post devo essere inizialmente 0' );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fileToBeUploaded[0][1], $fileToBeUploaded[0][2] ), 'un file valido non è stato salvato?' );
        $this->assertCount( 1, $secure_attachments->getAttachments(), 'il post contiene già un allegato' );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fileToBeUploaded[1][1], $fileToBeUploaded[1][2] ), 'un file valido non è stato salvato?' );
        $this->assertCount( 2, $secure_attachments->getAttachments(), 'gli allegati del post devo essere 2' );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fileToBeUploaded[2][1], $fileToBeUploaded[2][2] ), 'un file valido non è stato salvato?' );
        $this->assertCount( 3, $secure_attachments->getAttachments(), 'gli allegati del post devo essere 3' );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fileToBeUploaded[3][1], $fileToBeUploaded[3][2] ), 'un file valido non è stato salvato?' );
        $this->assertCount( 4, $secure_attachments->getAttachments(), 'gli allegati del post devo essere 4' );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fileToBeUploaded[4][1], $fileToBeUploaded[4][2] ), 'un file valido non è stato salvato?' );
        $this->assertCount( 5, $secure_attachments->getAttachments(), 'gli allegati del post devo essere 5' );

        $attachments = $secure_attachments->getAttachments();
        $this->assertEquals( 'documento di prova', $attachments[0]['file-title'] );
        $this->assertEquals( 'documento di prova 2', $attachments[2]['file-title'] );
        $this->assertEquals( 'documento di prova 3', $attachments[1]['file-title'] );
        $this->assertEquals( 'documento di prova 0', $attachments[3]['file-title'] );
        $this->assertEquals( 'documento di prova 4', $attachments[4]['file-title'] );

        return $secure_attachments;
    }

    /**
     * @depends test_order_saving_uploaded_file
     * @dataProvider sampleUploadedFileOK
     */
    function test_file_title_already_in_use( $file_title, $fake_uploaded_file, $fake_params, $secure_attachments ) {
        $this->assertFalse( $secure_attachments->validateUploadedFile( $fake_uploaded_file, $file_title ) );
        $this->assertArrayHasKey( 'file-title', $secure_attachments->getValidationErrors() );
    }

    /**
     * @depends test_order_saving_uploaded_file
     * @dataProvider sampleUploadedFileOK
     */
    function test_file_name_already_in_use( $file_title, $fake_uploaded_file, $fake_params, $secure_attachments ) {
        $file_title = "nuovo file mai usato"; //change file name with no one used t force error on filename
        $this->assertFalse( $secure_attachments->validateUploadedFile( $fake_uploaded_file, $file_title ) );
        $this->assertArrayHasKey( 'file-name', $secure_attachments->getValidationErrors() );
    }

    /**
     * @dataProvider sampleUploadedFileOK
     */
    function test_remove_file_attachment( $file_title, $fake_uploaded_file, $fake_params ) {
        $post_id = $this->factory->post->create();

        $secure_attachments = $this->secure_attachment_mock( $post_id );

        $this->assertTrue( $secure_attachments->saveUploadedFile( $fake_uploaded_file, $fake_params ), 'un file valido non è stato salvato?' );
        $this->assertCount( 1, $secure_attachments->getAttachments() );

        if( ! file_exists( $secure_attachments->getUploadDir() ) ){
            mkdir($secure_attachments->getUploadDir());
        }
        $filename = "prova.pdf";
        $test = dirname(__FILE__);
        copy( dirname(__FILE__) . '/../data/sample-file/' . $filename, $secure_attachments->getUploadDir() . $filename );

        $this->assertTrue( $secure_attachments->removeAttachment( $filename ) );
        $this->assertCount( 0, $secure_attachments->getAttachments() );

    }

    public function sampleUploadedFileOK() {
        $file_title = "documento di prova";
        $fake_uploaded_file = array(
            'name'     => 'prova.pdf',
            'type'     => 'application/pdf',
            'size'     => 958*1024,
            'error'    => 0,
            'tmp_name' => 'prova.pdf',
        );
        $fake_params = array(
            'file-title' => $file_title,
            'file-description' => 'descrizione di prova per completare il test con tutti i dati',
            'file-order' => 1,
        );

        return array(
            array($file_title, $fake_uploaded_file, $fake_params)
        );
   }

    public function sampleUploadedFileError() {

        // error upload
        $file_title1 = "documento di prova";
        $fake_uploaded_file1 = array(
            'name'     => 'prova.pdf',
            'type'     => 'application/pdf',
            'size'     => 958*1024,
            'error'    => 1,
            'tmp_name' => 'prova.pdf',
        );
        $error_message_index1 = 'upload-error';
        // file type and extension not valid
        $file_title2 = "documento di prova 2";
        $fake_uploaded_file2 = array(
            'name'     => 'prova2.doc',
            'type'     => 'application/doc',
            'size'     => 958*1024,
            'error'    => 0,
            'tmp_name' => 'prova2.doc',
        );
        $error_message_index2 = 'file-type';
        $file_title3 = "documento di prova 2";
        $fake_uploaded_file3 = array(
            'name'     => 'prova2.doc',
            'type'     => 'application/doc',
            'size'     => 958*1024,
            'error'    => 0,
            'tmp_name' => 'prova2.doc',
        );
        $error_message_index3 = 'file-extension';
        // file too big
        $file_title4 = "documento di prova 4";
        $fake_uploaded_file4 = array(
            'name'     => 'prova4.pdf',
            'type'     => 'application/pdf',
            'size'     => 3*1024*1024,
            'error'    => 0,
            'tmp_name' => 'prova4.pdf',
        );
        $error_message_index4 = 'file-size';
        return array(
            array($file_title1, $fake_uploaded_file1, $error_message_index1 ),
            array($file_title2, $fake_uploaded_file2, $error_message_index2 ),
            array($file_title3, $fake_uploaded_file3, $error_message_index3 ),
            array($file_title4, $fake_uploaded_file4, $error_message_index4 ),
        );

    }

    public function sampleUploadedFileSequenceOK() {
        $file_title1 = "documento di prova";
        $fake_uploaded_file1 = array(
            'name'     => 'prova.pdf',
            'type'     => 'application/pdf',
            'size'     => 958 * 1024,
            'error'    => 0,
            'tmp_name' => 'prova.pdf',
        );
        $fake_params1 = array(
            'file-title' => $file_title1,
            'file-description' => 'descrizione di prova per completare il test con tutti i dati',
            'file-order' => 1,
        );

        $file_title2 = "documento di prova 0";
        $fake_uploaded_file2 = array(
            'name'     => 'prova0.pdf',
            'type'     => 'application/pdf',
            'size'     => 958 * 1024,
            'error'    => 0,
            'tmp_name' => 'prova0.pdf',
        );
        $fake_params2 = array(
            'file-title' => $file_title2,
            'file-description' => 'descrizione di prova 90 per completare il test con tutti i dati',
            'file-order' => 0,
        );

        $file_title3 = "documento di prova 2";
        $fake_uploaded_file3 = array(
            'name'     => 'prova2.pdf',
            'type'     => 'application/pdf',
            'size'     => 123 * 1024,
            'error'    => 0,
            'tmp_name' => 'prova2.pdf',
        );
        $fake_params3 = array(
            'file-title' => $file_title3,
            'file-description' => 'descrizione di prova 2 per completare il test con tutti i dati',
            'file-order' => 5,
        );
        $file_title4 = "documento di prova 3";
        $fake_uploaded_file4 = array(
            'name'     => 'prova3.pdf',
            'type'     => 'application/pdf',
            'size'     => 342 * 1024,
            'error'    => 0,
            'tmp_name' => 'prova3.pdf',
        );
        $fake_params4 = array(
            'file-title' => $file_title4,
            'file-description' => 'descrizione di prova 3 per completare il test con tutti i dati',
            'file-order' => 2,
        );

        $file_title5 = "documento di prova 4";
        $fake_uploaded_file5 = array(
            'name'     => 'prova4.pdf',
            'type'     => 'application/pdf',
            'size'     => 342 * 1024,
            'error'    => 0,
            'tmp_name' => 'prova4.pdf',
        );
        $fake_params5 = array(
            'file-title' => $file_title5,
            'file-description' => 'descrizione di prova 4 per completare il test con tutti i dati',
        );

        return array(
            array($file_title1, $fake_uploaded_file1, $fake_params1),
            array($file_title2, $fake_uploaded_file2, $fake_params2),
            array($file_title3, $fake_uploaded_file3, $fake_params3),
            array($file_title4, $fake_uploaded_file4, $fake_params4),
            array($file_title5, $fake_uploaded_file5, $fake_params5),
        );
    }

    private function secure_attachment_mock( $post_id ) {
        $secure_attachments = $this->getMockBuilder('Secure_Attachments')
            ->setMethods(array('move_uploaded_file'))
            ->setConstructorArgs(array($post_id))
            ->getMock();

        $secure_attachments->expects($this->any())
            ->method('move_uploaded_file')
            ->will($this->returnValue(true));

        return $secure_attachments;

    }
}