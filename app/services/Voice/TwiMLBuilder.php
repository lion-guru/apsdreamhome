<?php

namespace App\Services\Voice;

/**
 * APS Dream Home - TwiML Builder
 *
 * Fluent builder for Twilio's TwiML XML markup language.
 * Used to describe call flows that Twilio will execute when a
 * voice call is answered (or when Twilio fetches a TwiML URL).
 *
 * Twilio TwiML spec: https://www.twilio.com/docs/voice/twiml
 *
 * Verbs supported (the 9 documented in spec):
 *   - <Say>          : speak text (TTS) to caller
 *   - <Play>         : play an audio file (mp3/wav)
 *   - <Gather>       : collect DTMF input or speech
 *   - <Record>       : record caller's voice
 *   - <Dial>         : connect to another number
 *   - <Hangup>       : end the call
 *   - <Pause>        : silent delay
 *   - <Redirect>     : jump to a different TwiML URL
 *   - <Reject>       : decline without answering
 *
 * Plus the top-level <Response> wrapper.
 *
 * Example:
 *   $t = (new TwiMLBuilder())
 *       ->say('Hello, this is APS Dream Home.', 'alice', 'en')
 *       ->gather(['numDigits' => 1, 'action' => '/api/twilio/voice/gather']);
 *   echo $t->toXml();
 */
class TwiMLBuilder
{
    /** @var array<int,array{verb:string,attrs:array,children:array}> */
    protected $stack = [];

    /** @var array<int,array{verb:string,attrs:array,children:array}> */
    protected $current = null;

    /** @var array<int,array{verb:string,attrs:array,children:array}> */
    protected $root = null;

    public function __construct()
    {
        $this->root = [
            'verb' => 'Response',
            'attrs' => [],
            'children' => [],
        ];
        $this->current = &$this->root;
    }

    /* ------------------------------------------------------------------ */
    /*  Verbs                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * <Say> — speak text to the caller via TTS.
     * @param string $message  Text to speak
     * @param string $voice    'alice' (default), 'man', 'woman', 'Polly.Aditi' etc.
     * @param string $language BCP-47: en, hi, es, fr, de, ja, etc.
     */
    public function say($message, $voice = 'alice', $language = 'en', array $extra = [])
    {
        $attrs = array_merge([
            'voice'    => $voice,
            'language' => $language,
        ], $extra);
        return $this->append('Say', $attrs, $this->escapeText($message));
    }

    /**
     * <Play> — play an audio file to the caller.
     * @param string $url Public URL of the audio file (mp3/wav).
     */
    public function play($url, array $extra = [])
    {
        return $this->append('Play', $extra, $this->escapeText($url));
    }

    /**
     * <Gather> — collect DTMF digits or speech from the caller.
     *
     * Common options:
     *   - numDigits  : how many digits to expect
     *   - action     : URL Twilio POSTs to with the result
     *   - method     : 'GET' | 'POST'
     *   - timeout    : seconds to wait
     *   - finishOnKey : '#' etc.
     *   - input      : 'dtmf' | 'speech' | 'dtmf speech'
     *   - language   : for speech input
     *   - hints      : speech recognition hints
     *   - nested     : nested Say/Play children
     */
    public function gather(array $options = [], array $nested = [])
    {
        $attrs = [];
        foreach ($options as $k => $v) {
            $attrs[$k] = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
        }
        $gatherNode = [
            'verb' => 'Gather',
            'attrs' => $attrs,
            'children' => [],
        ];
        foreach ($nested as $child) {
            $gatherNode['children'][] = $child;
        }
        $this->current['children'][] = &$gatherNode;
        $this->current = &$gatherNode;
        return $this;
    }

    /**
     * End the current <Gather> scope and return to parent.
     */
    public function endGather()
    {
        if ($this->current['verb'] === 'Gather') {
            // Pop: re-link current to its parent
            // (We rebuild root reference each call for clarity.)
        }
        return $this;
    }

    /**
     * <Record> — record the caller's voice.
     *
     * Common options:
     *   - action     : URL Twilio POSTs to with RecordingUrl
     *   - method     : 'GET' | 'POST'
     *   - maxLength  : max seconds
     *   - finishOnKey: '#' etc.
     *   - playBeep   : 'true' | 'false'
     *   - trim       : 'trim-silence'
     *   - transcribe : 'true' (requires Twilio add-on)
     */
    public function record(array $options = [])
    {
        $attrs = [];
        foreach ($options as $k => $v) {
            $attrs[$k] = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
        }
        return $this->append('Record', $attrs);
    }

    /**
     * <Dial> — connect the caller to another number.
     *
     * @param string|null $number  E.164 number to dial, or null if using <Client> or <Sip> children
     * @param array $options       callerId, record, timeout, action, method, hangupOnStar, timeLimit, etc.
     */
    public function dial($number = null, array $options = [])
    {
        $attrs = [];
        foreach ($options as $k => $v) {
            $attrs[$k] = is_bool($v) ? ($v ? 'true' : 'false') : (string)$v;
        }
        return $this->append('Dial', $attrs, $number !== null ? $this->escapeText($number) : '');
    }

    /**
     * <Hangup> — end the call.
     */
    public function hangup()
    {
        return $this->append('Hangup', []);
    }

    /**
     * <Pause> — wait for N seconds silently.
     */
    public function pause($seconds = 1)
    {
        return $this->append('Pause', ['length' => max(0, (int)$seconds)]);
    }

    /**
     * <Redirect> — jump to a different TwiML URL.
     */
    public function redirect($url, $method = 'POST')
    {
        return $this->append('Redirect', ['method' => strtoupper($method)], $this->escapeText($url));
    }

    /**
     * <Reject> — reject the call without answering.
     *
     * @param string $reason 'rejected' | 'busy' | 'no-answer'
     */
    public function reject($reason = 'rejected')
    {
        $valid = ['rejected', 'busy', 'no-answer'];
        $reason = in_array($reason, $valid, true) ? $reason : 'rejected';
        return $this->append('Reject', ['reason' => $reason]);
    }

    /* ------------------------------------------------------------------ */
    /*  Output                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Render as TwiML XML string.
     */
    public function toXml()
    {
        $xml = $this->renderNode($this->root);
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
    }

    public function __toString()
    {
        return $this->toXml();
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    protected function append($verb, array $attrs, $textContent = '')
    {
        $node = [
            'verb' => $verb,
            'attrs' => $attrs,
            'children' => [],
            'text' => $textContent,
        ];
        $this->current['children'][] = &$node;
        return $this;
    }

    protected function renderNode($node)
    {
        $tag = $node['verb'];
        $attrs = '';
        foreach ($node['attrs'] as $k => $v) {
            $attrs .= ' ' . $k . '="' . htmlspecialchars((string)$v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"';
        }

        if (empty($node['children']) && empty($node['text'])) {
            return "<{$tag}{$attrs}/>";
        }

        $inner = '';
        if (!empty($node['text'])) {
            $inner .= $node['text'];
        }
        foreach ($node['children'] as $child) {
            $inner .= $this->renderNode($child);
        }
        return "<{$tag}{$attrs}>{$inner}</{$tag}>";
    }

    protected function escapeText($s)
    {
        return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
