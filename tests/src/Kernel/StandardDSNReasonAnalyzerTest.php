<?php

namespace Drupal\Tests\inmail\Kernel;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\Parser;
use Drupal\inmail\Plugin\inmail\Analyzer\StandardDSNReasonAnalyzer;
use Drupal\inmail\ProcessorResult;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Unit tests for the DSN reason analyzer.
 *
 * @coversDefaultClass \Drupal\inmail\Plugin\inmail\Analyzer\StandardDSNReasonAnalyzer
 *
 * @group inmail
 */
class StandardDSNReasonAnalyzerTest extends KernelTestBase {

  public static $modules = ['inmail'];

  /**
   * Tests the analyze method.
   *
   * @covers ::analyze
   *
   * @dataProvider provideReasons
   */
  public function testAnalyze($filename, $expected_reason) {
    $message = (new Parser(new LoggerChannel('test')))->parseMessage($this->getRaw($filename));
    $analyzer = new StandardDSNReasonAnalyzer(array(), $this->randomMachineName(), array());
    $processor_result = new ProcessorResult();
    $processor_result->ensureAnalyzerResult(DefaultAnalyzerResult::TOPIC, DefaultAnalyzerResult::createFactory());

    $analyzer->analyze($message, $processor_result);
    /** @var \Drupal\inmail\DefaultAnalyzerResult $result */
    $result = $processor_result->getAnalyzerResult(DefaultAnalyzerResult::TOPIC);
    $bounce_data = $result->ensureContext('bounce', 'inmail_bounce');

    $bounce_context = $result->getContext('bounce');

    if (isset($expected_reason)) {
      $this->assertEquals($expected_reason, $bounce_data->getReason());
    }
    else {
      $this->assertFalse(is_null($bounce_context));

    }
  }

  /**
   * Provides expected DSN reason messages for test message files.
   */
  public function provideReasons() {
    return [
      ['accessdenied.eml',
        "This is the Postfix program at host kyle.greenpeace.org.

I'm sorry to have to inform you that your message could not
be delivered to one or more recipients. It's attached below.

For further assistance, please send mail to <postmaster>

If you do so, please include this problem report. You can
delete your own text from the attached returned message.

			The Postfix program

<user@example.org>: host mx1.example.org[62.94.82.91] said:
    554 5.7.1 <kyle.greenpeace.org[194.0.197.22]>: Client host rejected: Access
    denied (in reply to RCPT TO command)",
      ],
      ['full.eml',
        "- These recipients of your message have been processed by the mail server:
user@example.org; Failed; 4.2.2 (mailbox full)

    Remote MTA ms5.han.skanova.net: SMTP diagnostic: 552 RCPT TO:<masked4@pne.telia.com> Mailbox disk quota exceeded",
      ],
      ['normal.eml', NULL],
      ['nouser.eml',
        'Your message

  Subject: We want a toxic-free future

was not delivered to:

  environment@lvmh.fr

because:

  User environment (environment@lvmh.fr) not listed in Domino Directory',
      ],
    ];
  }

  /**
   * Returns the raw contents of a given test message file.
   *
   * @param string $filename
   *   The name of the file.
   *
   * @return string
   *   The message content.
   */
  protected function getRaw($filename) {
    $path = __DIR__ . '/../../modules/inmail_test/eml/' . $filename;
    return file_get_contents($path);
  }

}
