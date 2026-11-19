<?php
/** Verify the release delta ZIP/JSON against an isolated 2.2.14 theme and rollback. */
declare(strict_types=1);
$root = getenv('RS_PATCH_TEST_DIR');
$patchDir = getenv('RS_PATCH_RELEASE_DIR');
if (!$root || !is_dir($root . '/theme') || !$patchDir) throw new RuntimeException('Missing patch fixtures');
$manifest = json_decode((string) file_get_contents($patchDir . '/rangesadeghi-patch-2.2.15.json'), true);
if (!is_array($manifest) || ($manifest['min_version'] ?? '') !== '2.2.14' || ($manifest['target_version'] ?? '') !== '2.2.15') throw new RuntimeException('Invalid release manifest');
$before = [];
foreach ($manifest['files'] as $rel => $entry) $before[$rel] = is_file($root . '/theme/' . $rel) ? file_get_contents($root . '/theme/' . $rel) : null;
$blade = rs_app('blade'); $options = rs_app('options');
$app = new App\Core\Application($root . '/theme', '');
$app->instance('blade', $blade); $app->instance('options', $options);
$manager = new App\Updater\PatchManager($app);
foreach (['zip', 'json'] as $ext) {
    $result = $manager->applyPatchFile($patchDir . '/rangesadeghi-patch-2.2.15.' . $ext);
    if (empty($result['ok'])) throw new RuntimeException("Release $ext apply failed: " . ($result['message'] ?? ''));
    if (($result['changed_count'] ?? 0) !== count($manifest['files'])) throw new RuntimeException('Wrong number of patched files');
    foreach ($manifest['files'] as $rel => $entry) {
        $file = $root . '/theme/' . $rel;
        if (($entry['action'] ?? '') === 'delete') {
            if (file_exists($file)) throw new RuntimeException("Deletion failed: $rel");
        } elseif (!is_file($file) || hash_file('sha256', $file) !== $entry['sha256']) throw new RuntimeException("Wrong patch content: $rel");
    }
    $rollback = $manager->rollback((string) ($result['backup_id'] ?? ''));
    if (empty($rollback['ok'])) throw new RuntimeException("Release $ext rollback failed");
    foreach ($before as $rel => $bytes) {
        $file = $root . '/theme/' . $rel;
        if ($bytes === null ? file_exists($file) : (!is_file($file) || file_get_contents($file) !== $bytes)) throw new RuntimeException("Rollback mismatch: $rel");
    }
}
echo "Release ZIP and JSON patch application and rollback passed.\n";
