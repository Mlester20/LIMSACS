<?php

/**
 * Hash a password using bcrypt
 *
 * @param string $password The plain text password to hash
 * @return string|false The hashed password, or false on failure
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify a password against a hash
 *
 * @param string $password The plain text password to verify
 * @param string $hash The hashed password to verify against
 * @return bool True if the password matches the hash, false otherwise
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if a password hash needs to be rehashed
 *
 * @param string $hash The password hash to check
 * @return bool True if the hash needs to be rehashed, false otherwise
 */
function passwordNeedsRehash($hash)
{
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 10]);
}
