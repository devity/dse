#!/usr/bin/php
<?php
 
/* CONFIGURE THESE VARIABLES */

// Database connection
$mysql_user = 'root';
$mysql_pass = '';
$mysql_db = array();	// Array of all databases you want to backup
 
$ls_raw=`ls -l /var/lib/mysql | grep "^d"`;
$ls_array=split("\n",$ls_raw);
foreach($ls_array as $line){
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$line=str_replace("  "," ",$line);
	$parts=split(" ",$line);
	$db=$parts[8];
	if($db){
		print "Found DB: $db\n";
		$mysql_db[]=$db;
	}
}


// Place to store backup locally
$backup_path = '/backup/mysql';
// Email address to send report (optional)
$email_address = "";
// Amazon S3 info (optional)
//$s3bash_path = '.';
//$s3_key = 'YOUR-S3-ACCESS-KEY';
//$s3_secret = 'YOUR-S3-SECRET-KEY';
//$s3_bucket = 'YOUR-S3-BUCKET';
// Delete yesterday's backup?
$delete_yesterday = false;
// Delete local backup? (only if using s3)
$delete_local = false;
 
/* RUN THE BACKUP */
 
// Required file
 
// Time variables
$startime = time();
$yesterday = date('Ymd', strtotime('-1 day'));
$today = date('Ymd');
 
// Create today's folder
echo "Creating today's folder...n";
if (!file_exists("$backup_path/$today")) {
	mkdir("$backup_path/$today");
}
 
// Making today's backup
echo "Creating hot copy...n";
 
// Loop through each database and run mysqlhotcopy
$pass_clause="";
if($mysql_pass){
	$pass_clasue="--password=$mysql_pass";
}
foreach ($mysql_db as $dbname) {
	$cmd="mysqlhotcopy --user=$mysql_user $pass_clause $dbname $backup_path/$today/";
	print "running: $cmd \n";
	system($cmd);
}
 
// Tar and gzip today's backup
echo "Creating tarball...n";
$gzipfile = "$backup_path/${today}_backup.tgz";
system("tar -czvf $gzipfile $backup_path/$today/*");
// Get size of backup
$a = array("B", "KB", "MB", "GB", "TB", "PB");
$pos = 0;
$size = filesize($gzipfile);
while ($size >= 1024) {
  	$size /= 1024;
    $pos++;
}
$fsize = round($size,2)." ".$a[$pos];
 
// Remove yesterday's backup
if ($delete_yesterday) {
	if (file_exists("$backup_path/$yesterday")) {
		echo "Deleting local backup from yesterday...n";
		system("rm -Rf $backup_path/$yesterday/*");
		system("rmdir $backup_path/$yesterday");
	}
}
 
 
// Send to Amazon
if ($s3_key && $s3_secret) {
	// Upload today's file
	echo "Uploading to Amazon...n";
	// Instantiate s3 class
	$s3 = new S3($s3_key, $s3_secret);
	// Upload file
	if ($s3->putObjectFile($gzipfile, $s3_bucket, "mysql.$today.tgz", S3::ACL_PRIVATE)) {
		// Delete yesterday's file
		if ($delete_yesterday) {
			echo "Delete yesterday's backup on Amazon...n";
			$s3->deleteObject($s3_bucket, "mysql.$yesterday.tgz");
		}
		// Delete today's file
		if ($delete_local) {
			if (file_exists("$backup_path/$today")) {
				echo "Deleting local backup from today...n";
				system("rm -Rf $backup_path/$today/*");
				system("rmdir $backup_path/$today");
			}
		}
	}
}
 
 
// Email report
if ($email_address) {
	echo "Emailing report...n";
	$completed = (time() - $startime);
	$completed = floor($completed/60) . ' minutes and ' . ($completed%60) . ' seconds';
	$message = "Tonight's backup completed in $completed. Gzipped file size: $fsize"; 
	mail($email_address, "MySQL Backup - " . date('m/d/Y'), $message);
}
 
// Done
echo "Donen";
 
 
?>

