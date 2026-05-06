<?php


function sanitizeEmail(ServerRequestInterface $request): ?string
{
    $email = $request->getQueryParams()['email'] ?? null;

    if ($email === null) {
        return null;
    }

    $email = trim($email);
    $email = strtolower($email);

    if (hasBadWords($email)) {
        return null;
    }

    if (findInDb($email)) {
        return null;
    }

    return $email;
}

function hasBadWords(string $email): bool {}

function findInDb(string $email): bool {}

// --------------------------

function getUserEntity(int $id): UserEntity { }

function serializeUserDto(UserDto $user): string { }

function userEntityToDto(UserEntity $user): UserDto { }

// -------------------------------

interface Result {}
class Success implements Result {
    public function __construct(public readonly mixed $value) {}
}

interface Err extends Result {}
class StringTooShort implements Err {}
class UsernameExists implements Err {}
class InvalidCharacters implements Err {}
class DoesNotStartWithLetter implements Err {}

enum ValidationError {
  case StringTooShort;
  case UsernameExists;
  case InvalidCharacters;
  case DoesNotStartWithLetter;
}

// -------------------------------

function lengthCheck(string $s, int $len): string|ValidationError {
  return strlen($s) >= $len ? $s : ValidationError::StringTooShort;
}

function existsCheck(string $s): string|ValidationError {
  // Use real code here, obviously...
  return in_array($s, [...]) ? ValidationError::UsernameExists : $s;
}

function characterCheck(string $regex, string $s): string|ValidationError {
  return preg_match($regex, $s) ? $s : ValidationError::InvalidCharacters;
}

function startsWithLetter(string $s): string|ValidationError {
  return ctype_alpha($s[0]) ? $s : ValidationError::DoesNotStartWithLetter;
}

function either(\Closure $fn): \Closure {
  return static fn(mixed $val) => $val instanceof Err ? $val : $fn($val);
}

$result = 'ABC123'
    |> strtolower(...)
    |> lengthCheck(? , 3)
    |> existsCheck(...)
    |> characterCheck('/^[a-zA-Z0-9]*$/', ?))
    |> startsWithLetter(...)
;

match ($result) {
  ValidationError::StringTooShort => output('String must be 3 chars'),
  ValidationError::UsernameExists => output('That username already exists'),
  ValidationError::InvalidCharacters => output('Only alphanumeric chars are allowed'),
  ValidationError::DoesNotStartWithLetter => output('Name must start with a letter'),
  default => saveToDb($result),
};

// --------------------------

class ValidationLog {
  public bool $valid { get => $this->errors === []; }
  public function __construct(
    readonly public string $string,
    readonly public array  $errors = []
  ) {}
}

function logError(\Closure $fn): \Closure {
  return static function (mixed $val) use ($fn) {
    if (! $val instanceof ValidationLog) {
      $val = new ValidationLog($val);
    }
    $result = $fn($val->string);
    if ($result instanceof ValidationError) {
      return new ValidationLog($val->string, [...$val->errors, $result]);
    }
    return $val;
  };
}
