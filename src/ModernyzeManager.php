<?php

namespace Modernyze;

use Modernyze\Exceptions\ModernyzeException;
use ZipArchive;

class ModernyzeManager
{

    /**
     * Token for authorization.
     *
     * @var string
     */
    private string $token;

    /**
     * Secret for update validation.
     *
     * @var string
     */
    private string $secret;

    /**
     * Modernyze server base url.
     *
     * @var string
     */
    private string $url;

    /**
     * Sets the api token.
     *
     * @param string $token
     * @return static
     */
    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Sets the validation secret.
     *
     * @param string $secret
     * @return static
     */
    public function setSecret(string $secret): static
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Sets the api url.
     *
     * @param string $url
     * @return ModernyzeManager
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Makes a request to the api.
     *
     * @param string $url
     * @return array|null
     * @throws ModernyzeException
     */
    private function request(string $url): ?array
    {
        // perform data fetch
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $this->token]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        // handle error
        if (!is_array($data))
            throw new ModernyzeException("Non-JSON data returned by Modernyze endpoint. Ensure that [$url] is a valid url.");

        // return data
        return $data;
    }

    /**
     * Downloads a file.
     *
     * @param string $url
     * @param string $filename
     */
    private function download(string $url, string $filename)
    {
        // download update archive
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $this->token]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        $fp = fopen($filename, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    /**
     * Returns all products.
     *
     * @return array
     * @throws ModernyzeException
     */
    public function allProducts(): array
    {
        return $this->request(rtrim($this->url, "/") . "/api/products/all");
    }

    /**
     * Returns all versions of a product.
     *
     * @param string $product
     * @return array
     * @throws ModernyzeException
     */
    public function allVersions(string $product): array
    {
        return $this->request(rtrim($this->url, "/") . "/api/product/$product/versions");
    }

    /**
     * Returns the latest version of a product.
     *
     * @param string $product
     * @return string|null
     * @throws ModernyzeException
     */
    public function latestVersion(string $product): ?string
    {
        return $this->request(rtrim($this->url, "/") . "/api/product/$product/latest")["version"] ?? null;
    }

    /**
     * Returns the according hash and download link for a product.
     *
     * @param string $product
     * @param string $version
     * @return array
     * @throws ModernyzeException
     */
    public function versionInformation(string $product, string $version): array
    {
        return $this->request(rtrim($this->url, "/") . "/api/product/$product/$version");
    }

    /**
     * Downloads the given version of a product and validates the hash with the own secret.
     *
     * @param string $product
     * @param string $version
     * @param string $directory
     * @return string
     * @throws ModernyzeException
     */
    public function downloadAndVerify(string $product, string $version, string $directory): string
    {
        // get download information
        $information = $this->request(rtrim($this->url, "/") . "/api/product/$product/$version");

        // download file
        $filename = $directory . "/v$version.zip";
        $this->download($information["url"], $filename);

        // check hash
        $hash = hash_hmac("sha256", file_get_contents($filename), $this->secret);
        if ($hash !== $information["hash"]) {
            // hash is invalid
            unlink($filename);
            throw new ModernyzeException("Hash invalid. The downloaded update file seems to be corrupt and will be deleted.");
        }

        return $filename;
    }

    /**
     * Downloads the given version of a product, validates the hash with the own secret and installs the update in the
     * given directory.
     *
     * @param string $product
     * @param string $version
     * @param string $directory
     * @return bool
     * @throws ModernyzeException
     */
    public function update(string $product, string $version, string $directory): bool
    {
        // download and verify
        $filename = $this->downloadAndVerify($product, $version, $directory)    ;

        // install update
        $zip = new ZipArchive();

        // Zip File Name
        if ($zip->open($filename) === TRUE) {

            // Unzip Path
            $zip->extractTo($directory);
            $zip->close();
            unlink($filename);
            return true;
        } else {
            unlink($filename);
            throw new ModernyzeException("Failed to unzip update.");
        }
    }
}