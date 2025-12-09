<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новое сообщение обратной связи</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">
                                📬 Новое сообщение обратной связи
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Здравствуйте!
                            </p>
                            <p style="margin: 0 0 25px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                На сайте получено новое сообщение обратной связи. Ниже представлена информация о сообщении:
                            </p>
                            
                            <!-- Message Card -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-left: 4px solid #667eea; border-radius: 4px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                            <!-- Тип сообщения -->
                                            <tr>
                                                <td style="padding: 8px 0;">
                                                    <strong style="color: #667eea; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Тип:</strong>
                                                </td>
                                                <td style="padding: 8px 0; text-align: right;">
                                                    @if($feedback->is_anonymous)
                                                        <span style="display: inline-block; background-color: #ffc107; color: #000; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                                            Анонимное
                                                        </span>
                                                    @else
                                                        <span style="display: inline-block; background-color: #28a745; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                                            С контактами
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            
                                            <!-- Контактная информация -->
                                            @if(!$feedback->is_anonymous)
                                                @if($feedback->full_name)
                                                    <tr>
                                                        <td style="padding: 8px 0;">
                                                            <strong style="color: #495057; font-size: 14px;">ФИО:</strong>
                                                        </td>
                                                        <td style="padding: 8px 0; text-align: right; color: #212529;">
                                                            {{ $feedback->full_name }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                
                                                @if($feedback->phone)
                                                    <tr>
                                                        <td style="padding: 8px 0;">
                                                            <strong style="color: #495057; font-size: 14px;">Телефон:</strong>
                                                        </td>
                                                        <td style="padding: 8px 0; text-align: right; color: #212529;">
                                                            <a href="tel:{{ $feedback->phone }}" style="color: #667eea; text-decoration: none;">
                                                                {{ $feedback->phone }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                            
                                            <!-- Сообщение -->
                                            <tr>
                                                <td colspan="2" style="padding: 15px 0 0 0; border-top: 1px solid #dee2e6;">
                                                    <strong style="color: #495057; font-size: 14px; display: block; margin-bottom: 10px;">Сообщение:</strong>
                                                    <div style="background-color: #ffffff; padding: 15px; border-radius: 4px; color: #212529; font-size: 15px; line-height: 1.6; white-space: pre-wrap;">
                                                        {{ $feedback->message }}
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Дата и время -->
                                            <tr>
                                                <td colspan="2" style="padding: 15px 0 0 0; border-top: 1px solid #dee2e6;">
                                                    <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                                        <strong>Дата отправки:</strong> {{ $feedback->created_at->format('d.m.Y H:i') }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 25px 0 0 0; color: #6c757d; font-size: 14px; line-height: 1.6;">
                                Пожалуйста, проверьте сообщение и при необходимости свяжитесь с отправителем.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #dee2e6;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">
                                Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

