/// Bank Details Model
class BankDetails {
  final String? accountNumber;
  final String? accountHolderName;
  final String? bankName;
  final String? ifscCode;
  final String? branchName;
  final String? accountType; // savings, current
  final String? upiId;

  BankDetails({
    this.accountNumber,
    this.accountHolderName,
    this.bankName,
    this.ifscCode,
    this.branchName,
    this.accountType,
    this.upiId,
  });

  factory BankDetails.fromJson(Map<String, dynamic> json) {
    return BankDetails(
      accountNumber: json['accountNumber'] as String?,
      accountHolderName: json['accountHolderName'] as String?,
      bankName: json['bankName'] as String?,
      ifscCode: json['ifscCode'] as String?,
      branchName: json['branchName'] as String?,
      accountType: json['accountType'] as String?,
      upiId: json['upiId'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'accountNumber': accountNumber,
      'accountHolderName': accountHolderName,
      'bankName': bankName,
      'ifscCode': ifscCode,
      'branchName': branchName,
      'accountType': accountType,
      'upiId': upiId,
    };
  }

  BankDetails copyWith({
    String? accountNumber,
    String? accountHolderName,
    String? bankName,
    String? ifscCode,
    String? branchName,
    String? accountType,
    String? upiId,
  }) {
    return BankDetails(
      accountNumber: accountNumber ?? this.accountNumber,
      accountHolderName: accountHolderName ?? this.accountHolderName,
      bankName: bankName ?? this.bankName,
      ifscCode: ifscCode ?? this.ifscCode,
      branchName: branchName ?? this.branchName,
      accountType: accountType ?? this.accountType,
      upiId: upiId ?? this.upiId,
    );
  }
}
