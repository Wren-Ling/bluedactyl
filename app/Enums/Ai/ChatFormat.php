<?php

namespace Pterodactyl\Enums\Ai;

/**
 * Supported upstream chat API families.
 *
 * Every provider below speaks one of these five dialects. Keeping the request and
 * response shaping keyed off the dialect (rather than off the vendor) is what lets
 * the same conversation flow work across Anthropic, OpenAI, Gemini and Ollama.
 */
enum ChatFormat: string
{
    case ANTHROPIC_MESSAGES = 'anthropic_messages';
    case OPENAI_CHAT = 'openai_chat';
    case OPENAI_RESPONSES = 'openai_responses';
    case GEMINI_GENERATE_CONTENT = 'gemini_generate_content';
    case OLLAMA_CHAT = 'ollama_chat';

    public function label(): string
    {
        return match ($this) {
            self::ANTHROPIC_MESSAGES => 'Anthropic Messages',
            self::OPENAI_CHAT => 'OpenAI Chat Completions',
            self::OPENAI_RESPONSES => 'OpenAI Responses',
            self::GEMINI_GENERATE_CONTENT => 'Google generateContent',
            self::OLLAMA_CHAT => 'Ollama Chat',
        };
    }
}
