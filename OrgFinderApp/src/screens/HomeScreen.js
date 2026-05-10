import React, { useState, useEffect, useContext, useCallback } from 'react';
import {
    View, Text, TouchableOpacity, StyleSheet,
    FlatList, Image, ActivityIndicator, RefreshControl,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { AuthContext } from '../context/AuthContext';
import api from '../api/client';

export default function HomeScreen({ navigation }) {
    const { user } = useContext(AuthContext);
    const [recs, setRecs]             = useState([]);
    const [loading, setLoading]       = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const firstName = user?.first_name?.split(' ')[0] ?? 'Student';

    const loadRecs = useCallback(async () => {
        try {
            const res = await api.get('/recommendations');
            setRecs(res.data.recommendations);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, []);

    useEffect(() => { loadRecs(); }, []);

    const getMatchColor = (pct) => {
        if (pct >= 70) return '#16a34a';
        if (pct >= 40) return '#d97706';
        return '#4A6CF7';
    };

    const renderOrg = ({ item, index }) => (
        <TouchableOpacity
            style={styles.orgCard}
            onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
            activeOpacity={0.85}
        >
            {/* Rank badge */}
            <View style={styles.rankBadge}>
                <Text style={styles.rankText}>#{index + 1}</Text>
            </View>

            <View style={styles.cardTop}>
                {/* Logo */}
                <View style={styles.orgLogoWrap}>
                    {item.logo
                        ? <Image source={{ uri: item.logo }} style={styles.orgLogo} />
                        : <View style={[styles.orgLogo, styles.orgLogoFallback]}>
                            <Text style={styles.orgLogoText}>{item.name?.[0] ?? 'O'}</Text>
                          </View>
                    }
                </View>

                {/* Info */}
                <View style={styles.orgInfo}>
                    <Text style={styles.orgName} numberOfLines={2}>{item.name}</Text>
                    {item.category ? (
                        <Text style={styles.categoryTag}>{item.category}</Text>
                    ) : null}
                </View>

                {/* Match % */}
                {item.match_pct > 0 ? (
                    <View style={[styles.matchBadge, { borderColor: getMatchColor(item.match_pct) }]}>
                        <Text style={[styles.matchPct, { color: getMatchColor(item.match_pct) }]}>
                            {item.match_pct}%
                        </Text>
                        <Text style={[styles.matchLabel, { color: getMatchColor(item.match_pct) }]}>match</Text>
                    </View>
                ) : null}
            </View>

            {/* Match reason */}
            {item.match_reason ? (
                <Text style={styles.matchReason} numberOfLines={1}>{item.match_reason}</Text>
            ) : null}

            {/* Match tags */}
            {item.match_tags?.length > 0 ? (
                <View style={styles.tagsRow}>
                    {item.match_tags.slice(0, 3).map((tag, i) => (
                        <View key={i} style={styles.tag}>
                            <Text style={styles.tagText}>{tag}</Text>
                        </View>
                    ))}
                </View>
            ) : null}
        </TouchableOpacity>
    );

    return (
        <View style={styles.root}>
            {/* Header */}
            <View style={styles.header}>
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <View>
                            <Text style={styles.greeting}>Hello, {firstName}! 👋</Text>
                            <Text style={styles.subGreeting}>Find your perfect organization</Text>
                        </View>
                        <TouchableOpacity
                            style={styles.avatarBtn}
                            onPress={() => navigation.navigate('Profile')}
                        >
                            <Text style={styles.avatarText}>{firstName?.[0] ?? 'S'}</Text>
                        </TouchableOpacity>
                    </View>

                    {/* Quick actions */}
                    <View style={styles.quickActions}>
                        <TouchableOpacity style={styles.quickBtn} onPress={() => navigation.navigate('Events')}>
                            <Text style={styles.quickIcon}>📅</Text>
                            <Text style={styles.quickLabel}>Upcoming{'\n'}Events</Text>
                        </TouchableOpacity>
                        <View style={styles.divider} />
                        <TouchableOpacity style={styles.quickBtn} onPress={() => navigation.navigate('Orgs')}>
                            <Text style={styles.quickIcon}>🏛</Text>
                            <Text style={styles.quickLabel}>Explore{'\n'}CICS Orgs</Text>
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </View>

            {/* Recommendations */}
            <View style={styles.body}>
                <View style={styles.recHeader}>
                    <Text style={styles.recTitle}>Recommended for You</Text>
                    <Text style={styles.recSub}>Based on your interests & skills</Text>
                </View>

                {loading ? (
                    <ActivityIndicator style={{ marginTop: 40 }} color="#4A6CF7" size="large" />
                ) : (
                    <FlatList
                        data={recs}
                        keyExtractor={item => String(item.id)}
                        renderItem={renderOrg}
                        contentContainerStyle={styles.list}
                        showsVerticalScrollIndicator={false}
                        refreshControl={
                            <RefreshControl
                                refreshing={refreshing}
                                onRefresh={() => { setRefreshing(true); loadRecs(); }}
                                colors={['#4A6CF7']}
                            />
                        }
                        ListEmptyComponent={
                            <View style={styles.empty}>
                                <Text style={styles.emptyIcon}>🔍</Text>
                                <Text style={styles.emptyText}>No recommendations yet.</Text>
                                <Text style={styles.emptyHint}>Complete your profile to get personalized suggestions.</Text>
                                <TouchableOpacity onPress={() => navigation.navigate('Orgs')} style={styles.exploreBtn}>
                                    <Text style={styles.exploreBtnText}>Explore All Organizations</Text>
                                </TouchableOpacity>
                            </View>
                        }
                    />
                )}
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f0f2ff' },

    // Header
    header: { backgroundColor: '#4A6CF7', paddingHorizontal: 20, paddingBottom: 24 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 10 },
    greeting: { fontSize: 22, fontWeight: '800', color: '#fff' },
    subGreeting: { fontSize: 13, color: 'rgba(255,255,255,0.75)', marginTop: 2 },
    avatarBtn: {
        width: 40, height: 40, borderRadius: 20,
        backgroundColor: 'rgba(255,255,255,0.25)',
        alignItems: 'center', justifyContent: 'center',
    },
    avatarText: { color: '#fff', fontWeight: '700', fontSize: 16 },
    quickActions: {
        flexDirection: 'row',
        backgroundColor: 'rgba(255,255,255,0.15)',
        borderRadius: 16, marginTop: 16,
        paddingVertical: 14, paddingHorizontal: 20,
    },
    quickBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 10 },
    quickIcon: { fontSize: 22 },
    quickLabel: { color: '#fff', fontSize: 13, fontWeight: '600', lineHeight: 18 },
    divider: { width: 1, backgroundColor: 'rgba(255,255,255,0.3)', marginHorizontal: 16 },

    // Body
    body: { flex: 1 },
    recHeader: { paddingHorizontal: 20, paddingTop: 20, paddingBottom: 8 },
    recTitle: { fontSize: 18, fontWeight: '800', color: '#1e2f6e' },
    recSub: { fontSize: 12, color: '#94a3b8', marginTop: 2 },
    list: { paddingHorizontal: 16, paddingBottom: 30, gap: 12 },

    // Org card
    orgCard: {
        backgroundColor: '#fff',
        borderRadius: 16,
        padding: 14,
        shadowColor: '#4A6CF7',
        shadowOffset: { width: 0, height: 3 },
        shadowOpacity: 0.08,
        shadowRadius: 10,
        elevation: 3,
    },
    rankBadge: {
        position: 'absolute', top: 10, right: 10,
        backgroundColor: '#f0f2ff',
        borderRadius: 8, paddingHorizontal: 7, paddingVertical: 3,
    },
    rankText: { fontSize: 11, fontWeight: '700', color: '#4A6CF7' },
    cardTop: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 },
    orgLogoWrap: { marginRight: 12 },
    orgLogo: { width: 54, height: 54, borderRadius: 27 },
    orgLogoFallback: {
        backgroundColor: '#4A6CF7',
        alignItems: 'center', justifyContent: 'center',
    },
    orgLogoText: { color: '#fff', fontSize: 20, fontWeight: '700' },
    orgInfo: { flex: 1 },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 4, paddingRight: 50 },
    categoryTag: {
        alignSelf: 'flex-start',
        backgroundColor: '#eff6ff', borderRadius: 6,
        paddingHorizontal: 8, paddingVertical: 2,
        fontSize: 11, color: '#4A6CF7', fontWeight: '600',
    },

    // Match badge
    matchBadge: {
        borderWidth: 1.5, borderRadius: 10,
        paddingHorizontal: 8, paddingVertical: 4,
        alignItems: 'center', minWidth: 52,
    },
    matchPct: { fontSize: 15, fontWeight: '800' },
    matchLabel: { fontSize: 9, fontWeight: '600', textTransform: 'uppercase' },

    // Match reason & tags
    matchReason: { fontSize: 12, color: '#64748b', marginBottom: 8 },
    tagsRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    tag: {
        backgroundColor: '#f0fdf4', borderRadius: 20,
        paddingHorizontal: 10, paddingVertical: 4,
        borderWidth: 1, borderColor: '#bbf7d0',
    },
    tagText: { fontSize: 11, color: '#16a34a', fontWeight: '600' },

    // Empty
    empty: { alignItems: 'center', marginTop: 60, paddingHorizontal: 30 },
    emptyIcon: { fontSize: 40, marginBottom: 12 },
    emptyText: { fontSize: 16, fontWeight: '700', color: '#555' },
    emptyHint: { fontSize: 13, color: '#888', marginTop: 6, marginBottom: 20, textAlign: 'center' },
    exploreBtn: {
        backgroundColor: '#4A6CF7', borderRadius: 24,
        paddingHorizontal: 24, paddingVertical: 12,
    },
    exploreBtnText: { color: '#fff', fontWeight: '600', fontSize: 14 },
});
