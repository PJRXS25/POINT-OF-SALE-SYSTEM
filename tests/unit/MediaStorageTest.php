<?php

use App\Libraries\MediaStorage;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class MediaStorageTest extends CIUnitTestCase
{
    public function testLocalMediaCanBeStoredResolvedAndDeleted(): void
    {
        helper('media');
        putenv('media.storage=local');
        $_ENV['media.storage'] = 'local';

        $temporaryFile = tempnam(sys_get_temp_dir(), 'pos-media-');
        $this->assertNotFalse($temporaryFile);
        file_put_contents($temporaryFile, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));

        $upload = new class($temporaryFile, 'pixel.png', 'image/png', filesize($temporaryFile), UPLOAD_ERR_OK) extends UploadedFile {
            public function isValid(): bool
            {
                return true;
            }

            public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
            {
                $name ??= $this->getName();
                $destination = rtrim($targetPath, '/\\') . DIRECTORY_SEPARATOR . $name;
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
                if (!rename($this->getTempName(), $destination)) {
                    throw new RuntimeException('Test upload could not be moved.');
                }
                return true;
            }
        };
        $storage = new MediaStorage();
        $identifier = $storage->store($upload, 'products');

        $this->assertStringStartsWith('uploads/products/', $identifier);
        $this->assertNotNull(media_url($identifier));

        $storage->delete($identifier);
        $this->assertNull(media_url($identifier));
    }

    public function testInvalidDatabaseMediaIdentifierIsRejected(): void
    {
        helper('media');
        $this->assertNull(media_url('media:not-a-valid-token'));
    }
}
