<?php

require_once __DIR__ . '/../helpers/flashMessage.php';

    class ErrorHandler{

        /**
         * Build the consistent log line: timestamp, context, exception class,
         * message, file, line, and full stack trace.
         */
        private static function format(Throwable $e, string $context): string{
            $label = $context !== '' ? " [{$context}]" : '';

            return sprintf(
                "[%s]%s %s: %s in %s:%d\nStack trace:\n%s",
                date('Y-m-d H:i:s'),
                $label,
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
        }

        /**
         * Log the exception only. Use this where the current behavior is to
         * fail silently to the user (e.g. return false / null / []).
         * @param string $context e.g. "StudentsController::create"
         */
        public static function log(Throwable $e, string $context = ''): void{
            error_log(self::format($e, $context));
        }

        /**
         * Log the exception, set a safe flash error message, and redirect.
         * $userMessage is what the user sees — never the raw exception message.
         * @param string $location Full redirect URL (e.g. BASE_URL . '/resources/views/...')
         * @param string $userMessage Safe, user-facing message shown via the flash banner
         * @param string $context e.g. "StudentsController::create"
         */
        public static function redirect(
            Throwable $e,
            string $location,
            string $userMessage = 'Something went wrong. Please try again.',
            string $context = ''
        ): void{
            self::log($e, $context);
            FlashMessage::setFlash('error', $userMessage);
            header('Location: ' . $location);
            exit();
        }

        /**
         * Log the exception and return a safe message string for the caller
         * to use in a view or JSON response. $userMessage is what's returned —
         * never the raw exception message.
         * @param string $userMessage Safe, user-facing fallback message
         * @param string $context e.g. "DashboardController::index"
         * @return string
         */
        public static function safeMessage(
            Throwable $e,
            string $userMessage = 'Something went wrong. Please try again.',
            string $context = ''
        ): string{
            self::log($e, $context);
            return $userMessage;
        }
    }
