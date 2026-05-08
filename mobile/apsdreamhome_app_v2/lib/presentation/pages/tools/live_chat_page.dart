import 'dart:io';
import 'package:flutter/material.dart';
// import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';

/// Live Chat Page
/// In-app messaging with agents and support
class LiveChatPage extends StatefulWidget {
  const LiveChatPage({super.key});

  @override
  State<LiveChatPage> createState() => _LiveChatPageState();
}

class _LiveChatPageState extends State<LiveChatPage> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  // final ImagePicker _picker = ImagePicker();
  
  bool _isTyping = false;
  String _currentChat = 'support'; // support, agent, group
  
  // Sample chat data
  final List<Map<String, dynamic>> _chats = [
    {
      'id': 'support',
      'name': 'APS Support',
      'avatar': null,
      'lastMessage': 'How can I help you today?',
      'time': '10:30 AM',
      'unread': 0,
      'isOnline': true,
      'isSupport': true,
    },
    {
      'id': 'agent1',
      'name': 'Amit Sharma',
      'avatar': null,
      'lastMessage': 'The plot is still available. Would you like to visit?',
      'time': '09:45 AM',
      'unread': 2,
      'isOnline': true,
      'isSupport': false,
    },
    {
      'id': 'agent2',
      'name': 'Priya Patel',
      'avatar': null,
      'lastMessage': 'Commission has been processed.',
      'time': 'Yesterday',
      'unread': 0,
      'isOnline': false,
      'isSupport': false,
    },
    {
      'id': 'group1',
      'name': 'Associate Group',
      'avatar': null,
      'lastMessage': 'New colony launched in Lucknow!',
      'time': 'Yesterday',
      'unread': 5,
      'isOnline': true,
      'isGroup': true,
    },
  ];
  
  // Sample messages for current chat
  final List<Map<String, dynamic>> _messages = [
    {
      'id': '1',
      'text': 'Hello! Welcome to APS Dream Home. How can I assist you today?',
      'sender': 'support',
      'time': DateTime.now().subtract(const Duration(minutes: 5)),
      'type': 'text',
      'isMe': false,
    },
    {
      'id': '2',
      'text': 'Hi, I want to know about plots in Suryoday Heights.',
      'sender': 'user',
      'time': DateTime.now().subtract(const Duration(minutes: 4)),
      'type': 'text',
      'isMe': true,
    },
    {
      'id': '3',
      'text': 'Great choice! Suryoday Heights Phase 1 has 120 plots available starting from ₹3,000/sqft.',
      'sender': 'support',
      'time': DateTime.now().subtract(const Duration(minutes: 3)),
      'type': 'text',
      'isMe': false,
    },
    {
      'id': '4',
      'text': 'Amenities include park, 24/7 security, water supply, and electricity.',
      'sender': 'support',
      'time': DateTime.now().subtract(const Duration(minutes: 2)),
      'type': 'text',
      'isMe': false,
    },
    {
      'id': '5',
      'text': 'Would you like to schedule a site visit?',
      'sender': 'support',
      'time': DateTime.now().subtract(const Duration(minutes: 1)),
      'type': 'text',
      'isMe': false,
    },
  ];
  
  // Quick reply templates
  final List<String> _quickReplies = [
    'Tell me about plots',
    'Book site visit',
    'EMI options',
    'Commission details',
    'Contact sales team',
    'I have a complaint',
  ];

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _sendMessage() {
    if (_messageController.text.trim().isEmpty) return;
    
    setState(() {
      _messages.add({
        'id': DateTime.now().millisecondsSinceEpoch.toString(),
        'text': _messageController.text,
        'sender': 'user',
        'time': DateTime.now(),
        'type': 'text',
        'isMe': true,
      });
      _messageController.clear();
    });
    
    _scrollToBottom();
    
    // Simulate reply
    Future.delayed(const Duration(seconds: 1), () {
      _simulateReply();
    });
  }

  void _simulateReply() {
    setState(() {
      _isTyping = true;
    });
    
    Future.delayed(const Duration(seconds: 2), () {
      setState(() {
        _isTyping = false;
        _messages.add({
          'id': DateTime.now().millisecondsSinceEpoch.toString(),
          'text': 'Thank you for your message! Our team will get back to you shortly.',
          'sender': 'support',
          'time': DateTime.now(),
          'type': 'text',
          'isMe': false,
        });
      });
      _scrollToBottom();
    });
  }

  Future<void> _pickImage() async {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Image attachment is temporarily disabled.')),
    );
    /*
    final XFile? picked = await _picker.pickImage(source: ImageSource.gallery);
    if (picked != null) {
      setState(() {
        _messages.add({
          'id': DateTime.now().millisecondsSinceEpoch.toString(),
          'text': picked.path,
          'sender': 'user',
          'time': DateTime.now(),
          'type': 'image',
          'isMe': true,
        });
      });
      _scrollToBottom();
    }
    */
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: _buildAppBar(),
      body: Column(
        children: [
          // Chat List or Chat Messages
          Expanded(
            child: _currentChat == 'list'
                ? _buildChatList()
                : _buildChatMessages(),
          ),
          
          // Typing Indicator
          if (_isTyping)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              alignment: Alignment.centerLeft,
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade200,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _buildDot(0),
                        _buildDot(1),
                        _buildDot(2),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          
          // Quick Replies
          if (!_isTyping && _currentChat != 'list')
            _buildQuickReplies(),
          
          // Input Area
          if (_currentChat != 'list')
            _buildInputArea(),
        ],
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    if (_currentChat == 'list') {
      return AppBar(
        title: const Text('Messages'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () {},
          ),
          IconButton(
            icon: const Icon(Icons.more_vert),
            onPressed: () {},
          ),
        ],
      );
    }
    
    final currentChatData = _chats.firstWhere((c) => (c['id'] as String) == _currentChat);
    final bool isGroup = currentChatData['isGroup'] == true;
    final bool isOnline = currentChatData['isOnline'] == true;
    
    return AppBar(
      backgroundColor: Colors.blue.shade700,
      foregroundColor: Colors.white,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () {
          setState(() {
            _currentChat = 'list';
          });
        },
      ),
      title: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Icon(
              isGroup ? Icons.group : Icons.person,
              color: Colors.blue.shade700,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  currentChatData['name'] as String,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  isOnline ? 'Online' : 'Offline',
                  style: TextStyle(
                    fontSize: 12,
                    color: isOnline
                        ? Colors.green.shade300
                        : Colors.white70,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.call),
          onPressed: () {},
        ),
        IconButton(
          icon: const Icon(Icons.videocam),
          onPressed: () {},
        ),
        IconButton(
          icon: const Icon(Icons.more_vert),
          onPressed: () {},
        ),
      ],
    );
  }

  Widget _buildChatList() {
    return ListView.builder(
      itemCount: _chats.length,
      itemBuilder: (context, index) {
        final chat = _chats[index];
        final bool isSupport = chat['isSupport'] == true;
        final bool isGroup = chat['isGroup'] == true;
        final bool isOnline = chat['isOnline'] == true;
        final int unreadCount = (chat['unread'] as num).toInt();

        return ListTile(
          leading: Stack(
            children: [
              CircleAvatar(
                backgroundColor: isSupport
                    ? Colors.blue.shade100
                    : Colors.grey.shade200,
                child: Icon(
                  isGroup
                      ? Icons.group
                      : (isSupport
                          ? Icons.support_agent
                          : Icons.person),
                  color: isSupport
                      ? Colors.blue.shade700
                      : Colors.grey.shade700,
                ),
              ),
              if (isOnline)
                Positioned(
                  right: 0,
                  bottom: 0,
                  child: Container(
                    width: 12,
                    height: 12,
                    decoration: BoxDecoration(
                      color: Colors.green,
                      border: Border.all(color: Colors.white, width: 2),
                      borderRadius: BorderRadius.circular(6),
                    ),
                  ),
                ),
            ],
          ),
          title: Text(
            chat['name'] as String,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          subtitle: Text(
            chat['lastMessage'] as String,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          trailing: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                chat['time'] as String,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
              if (unreadCount > 0)
                Container(
                  margin: const EdgeInsets.only(top: 4),
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade700,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '$unreadCount',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                    ),
                  ),
                ),
            ],
          ),
          onTap: () {
            setState(() {
              _currentChat = chat['id'] as String;
            });
          },
        );
      },
    );
  }

  Widget _buildChatMessages() {
    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: _messages.length,
      itemBuilder: (context, index) {
        final message = _messages[index];
        return _buildMessageBubble(message);
      },
    );
  }

  Widget _buildMessageBubble(Map<String, dynamic> message) {
    final isMe = message['isMe'] as bool;
    final time = message['time'] as DateTime;
    
    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.75,
        ),
        child: Column(
          crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isMe ? Colors.blue.shade700 : Colors.grey.shade200,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(isMe ? 16 : 4),
                  bottomRight: Radius.circular(isMe ? 4 : 16),
                ),
              ),
              child: message['type'] == 'image'
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(
                        File(message['text'] as String),
                        width: 200,
                        fit: BoxFit.cover,
                      ),
                    )
                  : Text(
                      message['text'] as String,
                      style: TextStyle(
                        color: isMe ? Colors.white : Colors.black,
                      ),
                    ),
            ),
            const SizedBox(height: 4),
            Text(
              DateFormat('h:mm a').format(time),
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDot(int index) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(horizontal: 2),
      width: 8,
      height: 8,
      decoration: BoxDecoration(
        color: Colors.grey.shade600,
        borderRadius: BorderRadius.circular(4),
      ),
    );
  }

  Widget _buildQuickReplies() {
    return Container(
      height: 50,
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: _quickReplies.length,
        itemBuilder: (context, index) {
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: ActionChip(
              label: Text(_quickReplies[index]),
              onPressed: () {
                setState(() {
                  _messageController.text = _quickReplies[index];
                });
              },
              backgroundColor: Colors.blue.shade50,
            ),
          );
        },
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            IconButton(
              icon: const Icon(Icons.attach_file),
              onPressed: _pickImage,
            ),
            Expanded(
              child: TextField(
                controller: _messageController,
                decoration: InputDecoration(
                  hintText: 'Type a message...',
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 14,
                  ),
                ),
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
            const SizedBox(width: 8),
            GestureDetector(
              onTap: _sendMessage,
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade700,
                  borderRadius: BorderRadius.circular(24),
                ),
                child: const Icon(
                  Icons.send,
                  color: Colors.white,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
