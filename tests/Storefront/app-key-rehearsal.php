<?php

// Disposable cryptographic fixtures only. Does not boot Laravel or read application keys/data.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;

$oldKey = random_bytes(32);
$newKey = random_bytes(32);
$old = new Encrypter($oldKey, 'AES-256-CBC');
$new = new Encrypter($newKey, 'AES-256-CBC');
$bridge = (new Encrypter($newKey, 'AES-256-CBC'))->previousKeys([$oldKey]);
$checks = [];
$check = function (string $name, bool $passed) use (&$checks): void {
    $checks[$name] = $passed;
    if (! $passed) {
        throw new RuntimeException('Disposable key rehearsal failed: '.$name);
    }
};
$id = $old->encrypt(123);
$check('previous_key_reads_old_serialized_identifier', $bridge->decrypt($id) === 123);
$newId = $bridge->encrypt(123);
$check('writes_use_new_key', $new->decrypt($newId) === 123);
try {
    $new->decrypt($id);
    $check('old_identifier_needs_compatibility_or_reissue', false);
} catch (DecryptException $error) {
    $check('old_identifier_needs_compatibility_or_reissue', true);
}
try {
    $old->decrypt($newId);
    $check('old_only_rollback_cannot_read_new_writes', false);
} catch (DecryptException $error) {
    $check('old_only_rollback_cannot_read_new_writes', true);
}
foreach (['raw' => false, 'serialized' => true] as $kind => $serialized) {
    $plaintext = '{"sample":"fictional encrypted field","amount_minor":12345}';
    $ciphertext = $old->encrypt($plaintext, $serialized);
    // Preserve the decrypted bytes; never deserialize untrusted old application payloads.
    $converted = $new->encrypt($old->decrypt($ciphertext, false), false);
    $check($kind.'_bytes_preserved', hash_equals($old->decrypt($ciphertext, false), $new->decrypt($converted, false)));
    $check($kind.'_semantic_value_preserved', $new->decrypt($converted, $serialized) === $plaintext);
}
echo json_encode(['scope' => 'Ephemeral keys and synthetic values only; real APP_KEY/config/database were not read or modified.',
    'checks_passed' => count($checks), 'checks' => $checks, 'production_rotation_authorized' => false], JSON_PRETTY_PRINT).PHP_EOL;
