import os

voice_services = [
    'VoiceCallService',
    'TwilioVoiceService',
    'AIVoicePipeline',
    'AsteriskService',
    'OLNService',
    'VoiceProviderManager',
    'CRMVoiceService',
    'VoiceAssistantService',
    'VoiceProviderInterface',
]

for root, dirs, files in os.walk('app'):
    for f in files:
        if f.endswith('.php'):
            filepath = os.path.join(root, f)
            try:
                with open(filepath, 'r', errors='ignore') as fh:
                    content = fh.read()
                    for svc in voice_services:
                        lines = content.split('\n')
                        for j, line in enumerate(lines, 1):
                            if svc in line and 'use App' not in line and 'class ' not in line and 'namespace ' not in line:
                                print(f'{filepath}:{j}: {line.strip()[:150]}')
            except:
                pass
