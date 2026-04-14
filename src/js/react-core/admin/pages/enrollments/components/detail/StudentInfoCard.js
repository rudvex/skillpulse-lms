/**
 * Student Information Card Component
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";

const StudentInfoCard = ({ enrollment }) => {
    if (!enrollment) return null;

    const userEditUrl = `${SPLMSCore_Data?.adminUrl || '/wp-admin'}/user-edit.php?user_id=${enrollment.user_id}`;
    const userEnrollmentsUrl = `${SPLMSCore_Data?.adminUrl || '/wp-admin'}/admin.php?page=splms-enrollments&user_id=${enrollment.user_id}`;

    return (
        <Card>
            <CardHeader>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <SplmsIcon mode="wp" icon="admin-users" size={20} />
                    <h3 style={{ margin: 0 }}>{__('Student Information', 'skillpulse-lms')}</h3>
                </div>
            </CardHeader>
            <CardBody>
                <div style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                    <div style={{ flexShrink: 0 }}>
                        <img
                            src={enrollment.user_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(enrollment.user_name || 'Student')}&background=7e75ff&color=fff&size=80`}
                            alt={enrollment.user_name || 'Student'}
                            style={{
                                width: '80px',
                                height: '80px',
                                borderRadius: '50%',
                                objectFit: 'cover'
                            }}
                            onError={(e) => {
                                e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(enrollment.user_name || 'Student')}&background=7e75ff&color=fff&size=80`;
                            }}
                        />
                    </div>
                    <div style={{ flex: 1 }}>
                        <h4 style={{ margin: '0 0 8px 0' }}>
                            <a
                                href={userEditUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                style={{ textDecoration: 'none', color: 'inherit' }}
                            >
                                {enrollment.user_name || __('Unknown Student', 'skillpulse-lms')}
                                <span style={{ marginLeft: '5px', fontSize: '0.8em' }}>↗</span>
                            </a>
                        </h4>
                        <p style={{ margin: '0 0 8px 0', color: 'var(--splms-text-muted, #666)' }}>
                            <a href={`mailto:${enrollment.user_email}`} style={{ color: 'var(--splms-primary, #2271b1)' }}>
                                {enrollment.user_email || __('No email available', 'skillpulse-lms')}
                            </a>
                        </p>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', fontSize: '0.9em', color: 'var(--splms-text-muted, #666)' }}>
                            <div>
                                <strong>{__('User ID:', 'skillpulse-lms')}</strong> #{enrollment.user_id}
                            </div>
                            {enrollment.user_role && (
                                <div>
                                    <strong>{__('Role:', 'skillpulse-lms')}</strong> {enrollment.user_role}
                                </div>
                            )}
                        </div>
                        <div style={{ marginTop: '12px', display: 'flex', gap: '8px' }}>
                            <a
                                href={userEditUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                style={{
                                    fontSize: '0.875em',
                                    color: 'var(--splms-primary, #2271b1)',
                                    textDecoration: 'none'
                                }}
                            >
                                {__('View User Profile', 'skillpulse-lms')} →
                            </a>
                            <span style={{ color: 'var(--splms-border-color-light, #ddd)' }}>|</span>
                            <a
                                href={userEnrollmentsUrl}
                                style={{
                                    fontSize: '0.875em',
                                    color: 'var(--splms-primary, #2271b1)',
                                    textDecoration: 'none'
                                }}
                            >
                                {__('View All Enrollments', 'skillpulse-lms')} →
                            </a>
                        </div>
                    </div>
                </div>
            </CardBody>
        </Card>
    );
};

export default StudentInfoCard;

