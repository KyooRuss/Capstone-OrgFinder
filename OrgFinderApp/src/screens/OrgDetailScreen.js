import React, { useState, useEffect } from 'react';
import {
    View, Text, ScrollView, Image, StyleSheet,
    TouchableOpacity, ActivityIndicator, FlatList, Dimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import api from '../api/client';

const { width } = Dimensions.get('window');

export default function OrgDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [org, setOrg]       = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get(`/organizations/${id}`)
            .then(res => setOrg(res.data.organization))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [id]);

    if (loading) return (
        <View style={styles.center}>
            <ActivityIndicator color="#4A6CF7" size="large" />
        </View>
    );

    if (!org) return (
        <View style={styles.center}><Text>Organization not found.</Text></View>
    );

    return (
        <ScrollView style={styles.root} showsVerticalScrollIndicator={false}>
            <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
            {/* Hero header */}
            <View style={styles.hero}>
                <SafeAreaView>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Text style={styles.backIcon}>‹</Text>
                        <Text style={styles.backLabel}>{org.name}</Text>
                    </TouchableOpacity>
                </SafeAreaView>
                
                <View style={styles.heroContent}>
                    {org.logo
                        ? <Image source={{ uri: org.logo }} style={styles.heroLogo} />
                        : <View style={[styles.heroLogo, styles.heroLogoFallback]}>
                            <Text style={styles.heroLogoText}>{org.name?.[0] ?? 'O'}</Text>
                          </View>
                    }
                    <View style={styles.heroTextBlock}>
                        <Text style={styles.heroName} numberOfLines={2}>{org.name}</Text>
                        {org.president ? (
                            <Text style={styles.heroPresident}>President: {org.president}</Text>
                        ) : null}
                    </View>
                </View>
            </View>
            </LinearGradient>
            <View style={styles.body}>
                {/* Description */}
                {org.mission ? (
                    <View style={styles.section}>
                        <Text style={styles.sectionTitle}>About</Text>
                        <Text style={styles.descText}>{org.mission}</Text>
                    </View>
                ) : null}

                {/* Why Join */}
                {org.reasons?.length > 0 && (
                    <View style={styles.section}>
                        <Text style={styles.sectionTitle}>Why Join {org.name}?</Text>
                        {org.reasons.map((r, i) => (
                            <View key={i} style={styles.reasonRow}>
                                <Text style={styles.reasonCheck}>✔</Text>
                                <Text style={styles.reasonText}>{r}</Text>
                            </View>
                        ))}
                    </View>
                )}

                {/* Events & Activities photos */}
                {org.photos?.length > 0 && (
                    <View style={styles.section}>
                        <Text style={styles.sectionTitle}>Events & Activities</Text>
                        <ScrollView
                            horizontal
                            showsHorizontalScrollIndicator={false}
                            nestedScrollEnabled
                            contentContainerStyle={styles.photosRow}
                        >
                            {org.photos.map((p, i) => (
                                <Image key={i} source={{ uri: p }} style={styles.photo} />
                            ))}
                        </ScrollView>
                    </View>
                )}

                {/* Testimonials */}
                {org.testimonials?.length > 0 && (
                    <View style={styles.section}>
                        <View style={styles.sectionHeaderBtn}>
                            <Text style={styles.sectionTitleWhite}>Members' Experience</Text>
                        </View>
                        {org.testimonials.map((t, i) => (
                            <View key={i} style={styles.testimonialCard}>
                                <Text style={styles.testimonialText}>"{t.text}"</Text>
                                {t.author ? (
                                    <Text style={styles.testimonialAuthor}>— {t.author}</Text>
                                ) : null}
                            </View>
                        ))}
                    </View>
                )}

                {/* Core info */}
                <View style={styles.coreSection}>
                    <View style={styles.coreBadge}>
                        <Text style={styles.coreBadgeText}>Core Information</Text>
                    </View>
                    {org.vision ? (
                        <>
                            <Text style={styles.coreLabel}>Vision</Text>
                            <Text style={styles.coreText}>{org.vision}</Text>
                        </>
                    ) : null}
                    {org.mission ? (
                        <>
                            <Text style={styles.coreLabel}>Mission</Text>
                            <Text style={styles.coreText}>{org.mission}</Text>
                        </>
                    ) : null}
                </View>

                {/* Footer contact info */}
                {(org.room_number || org.contact_telegram || org.contact_facebook) && (
                    <View style={styles.footer}>
                        <View style={styles.footerRow}>
                            {org.room_number && <Text style={styles.footerItem}>📍 {org.room_number}</Text>}
                            {org.contact_telegram && <Text style={styles.footerItem}>📱 {org.contact_telegram}</Text>}
                            {org.contact_facebook && <Text style={styles.footerItem}>👤 {org.contact_facebook}</Text>}
                        </View>
                    </View>
                )}
            </View>
        </ScrollView>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
    center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
    hero: { paddingBottom: 24 },
    backBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 18 },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28, marginRight: 4 },
    backLabel: { color: '#fff', fontSize: 14, fontWeight: '600' },
    heroContent: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 20, gap: 16,
    },
    heroLogo: { width: 72, height: 72, borderRadius: 36, borderWidth: 3, borderColor: 'rgba(255,255,255,0.5)' },
    heroLogoFallback: { backgroundColor: 'rgba(255,255,255,0.25)', alignItems: 'center', justifyContent: 'center' },
    heroLogoText: { color: '#fff', fontSize: 28, fontWeight: '700' },
    heroTextBlock: { flex: 1 },
    heroName: { fontSize: 20, fontWeight: '800', color: '#fff', marginBottom: 4 },
    heroPresident: { fontSize: 13, color: 'rgba(255,255,255,0.75)', fontStyle: 'italic' },
    body: { padding: 16 },
    section: { backgroundColor: '#fff', borderRadius: 14, padding: 16, marginBottom: 14, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.06, shadowRadius: 6 },
    sectionTitle: { fontSize: 16, fontWeight: '700', color: '#1e2f6e', marginBottom: 12 },
    reasonRow: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 8, gap: 8 },
    reasonCheck: { fontSize: 14 },
    reasonText: { flex: 1, fontSize: 14, color: '#444', lineHeight: 20 },
    photosRow: { gap: 10, paddingHorizontal: 2 },
    photo: { width: 180, height: 120, borderRadius: 10 },
    sectionHeaderBtn: { backgroundColor: '#4A6CF7', borderRadius: 8, padding: 10, marginBottom: 12, alignSelf: 'flex-start' },
    sectionTitleWhite: { color: '#fff', fontWeight: '700', fontSize: 14 },
    descText: { fontSize: 14, color: '#555', lineHeight: 22 },
    testimonialCard: {
        backgroundColor: '#eef2ff', borderRadius: 10, padding: 14,
        marginBottom: 10, borderLeftWidth: 3, borderLeftColor: '#4A6CF7',
    },
    testimonialText: { fontSize: 13, color: '#333', lineHeight: 20, fontStyle: 'italic' },
    testimonialAuthor: { fontSize: 12, color: '#4A6CF7', fontWeight: '600', marginTop: 8, textAlign: 'right' },
    coreSection: { backgroundColor: '#fff', borderRadius: 14, padding: 16, marginBottom: 14, elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.06, shadowRadius: 6 },
    coreBadge: { backgroundColor: '#4A6CF7', alignSelf: 'flex-start', borderRadius: 8, paddingHorizontal: 22, paddingVertical: 10, marginBottom: 14 },
    coreBadgeText: { color: '#fff', fontWeight: '700', fontSize: 14 },
    coreLabel: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 6, marginTop: 8 },
    coreText: { fontSize: 14, color: '#555', lineHeight: 21 },
    footer: { alignItems: 'center', backgroundColor: '#1e2f6e', borderRadius: 14, padding: 16, marginBottom: 20 },
    footerRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 25 },
    footerItem: { color: 'rgba(255,255,255,0.8)', fontSize: 12 },
});
