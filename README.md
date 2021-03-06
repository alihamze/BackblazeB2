# Backblaze B2 PHP SDK
This is a **work in progress** PHP SDK for Backblaze B2. Not all parameters are in the examples below.

```php
use TechYet\B2\Client;

$client = new Client('accountId', 'applicationKey');

//Returns an array of Bucket objects indexed by the bucket name
$buckets = $client->listBuckets();

//Returns an array of File objects
$files = $buckets['testBucket']->listFileNames();

//Returns a File object
$file = $buckets['testBucket']->getFileByName('test.txt');

//Returns the file contents
$content = $file->download();

//Also returns the file contents
$content = $buckets['testBucket']->downloadFileByName('test.txt');

//Saves the file to disk and returns a success boolean
$success = $file->download([
    'SaveAs' => '/path/to/save/location',
]);

//Also saves the file to disk and returns a success boolean
$file = $buckets['testBucket']->downloadFileByName('test.txt', [
    'SaveAs' => '/path/to/save/location',
]);

//Returns a boolean value
$exists = $buckets['testBucket']->fileExists('test.txt');

//Returns a boolean value
$deleted = $buckets['testBucket']->getFileByName('test.txt')->delete();

//Returns a string containing the authorization token
$token = $buckets['testBucket']->getFileByName('test.txt')->getDownloadAuthorization([
    'validDurationInSeconds' => 86400,
]);

//Returns a file object
$file = $buckets['testBucket']->uploadFile('test.txt.', 'This is a test');
//You can also pass a file resource
$file = $buckets['testBucket']->uploadFile('test.txt.', fopen('/path/to/input', 'r'));
```

# API Endpoints
- [x] b2_authorize_account
- [ ] b2_cancel_large_file
- [ ] b2_create_bucket
- [ ] b2_create_key
- [ ] b2_delete_bucket
- [ ] b2_delete_file_version
- [x] b2_delete_key
- [x] b2_download_file_by_id
- [x] b2_download_file_by_name
- [x] b2_get_download_authorization
- [ ] b2_finish_large_file
- [ ] b2_get_file_info
- [ ] b2_get_upload_part_url
- [x] b2_get_upload_url
- [ ] b2_hide_file
- [x] b2_list_buckets
- [x] b2_list_file_names
- [ ] b2_list_file_versions
- [ ] b2_list_keys
- [ ] b2_list_parts
- [ ] b2_list_unfinished_large_files
- [ ] b2_start_large_file
- [ ] b2_update_bucket
- [x] b2_upload_file
- [ ] b2_upload_part
