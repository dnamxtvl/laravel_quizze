<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Mail;

Route::get('/healthcheck', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::get('/debug-ses', function() {
    try {
        // Test AWS credentials
        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
        ]);
        
        $buckets = $s3->listBuckets();
        
        // Test SES
        $ses = new \Aws\Ses\SesClient([
            'version' => '2010-12-01',
            'region'  => 'us-east-1', // Thử region khác
        ]);
        
        $quota = $ses->getSendQuota();
        
        return response()->json([
            's3_working' => true,
            'ses_working' => true,
            'ses_quota' => $quota,
            'aws_region' => env('AWS_DEFAULT_REGION'),
            'mail_driver' => env('MAIL_MAILER')
        ]);
        
    } catch (\Exception $e) {
        \Log::error('SES Debug Error: ' . $e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'aws_region' => env('AWS_DEFAULT_REGION'),
            'mail_driver' => env('MAIL_MAILER')
        ]);
    }
});

Route::get('/test/', function () {
    dd([
        'mail_config' => config('mail'),
        'ses_config' => config('services.ses'),
        'aws_region' => config('aws.region'),
        'env_region' => env('AWS_DEFAULT_REGION')
    ]);
    Log::error('test error channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});

Route::get('/test-mail/', function () {
    $email = 'hoangthanh22082000@gmail.com';
    Mail::raw('This is a test email from Laravel SES', function ($message) use ($email) {
        $message->to($email)
            ->subject('Test Email from Laravel');
    });
    Log::error('test error channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
