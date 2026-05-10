import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, TextInput, TouchableOpacity, StyleSheet,
    FlatList, Image, ActivityIndicator, RefreshControl,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import api from '../api/client';

const CATEGORIES = ['All', 'Technology', 'Arts', 'Leadership', 'Design', 'Gaming', 'Cybersecurity', 'E-sports'];

export default function OrganizationsScreen({ navigation }) {
    const [orgs, setOrgs]         = useState([]);
    const [loading, setLoading]   = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch]     = useState('');
    const [category, setCategory] = useState('All');

    const loadOrgs = useCallback(async () => {
        try {
            const params = {};
            if (search.trim()) params.search = search.trim();
            if (category !== 'All') params.category = category;
            const res = await api.get('/organizations', { params });
            setOrgs(res.data.organizations);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, [search, category]);

    useEffect(() => { setLoading(true); loadOrgs(); }, [category]);

    const renderOrg = ({ item }) => (
        <TouchableOpacity
            style={styles.orgCard}
            onPress={() => navigation.navigate('OrgDetail', { id: item.id })}
            activeOpacity={0.85}
        >
            <View style={styles.orgLogoWrap}>
                {item.logo
                    ? <Image source={{ uri: item.logo }} style={styles.orgLogo} />
                    : <View style={[styles.orgLogo, styles.orgLogoFallback]}>
                        <Text style={styles.orgLogoText}>{item.name?.[0] ?? 'O'}</Text>
                      </View>
                }
            </View>
            <Text style={styles.orgName} numberOfLines={2}>{item.name}</Text>
            {item.president ? (
                <Text style={styles.orgPresident} numberOfLines={1}>👤 {item.president}</Text>
            ) : null}
            {item.mission ? (
                <Text style={styles.orgMission} numberOfLines={2}>{item.mission}</Text>
            ) : null}
            <Text style={styles.viewDetails}>👁 View Details</Text>
        </TouchableOpacity>
    );

    return (
        <View style={styles.root}>
            <View style={styles.header}>
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                            <Text style={styles.backIcon}>‹</Text>
                        </TouchableOpacity>
                        <Text style={styles.headerTitle}>Organization</Text>
                    </View>

                    {/* Search */}
                    <View style={styles.searchRow}>
                        <View style={styles.searchWrap}>
                            <Text style={styles.searchIcon}>🔍</Text>
                            <TextInput
                                style={styles.searchInput}
                                placeholder="Search Organization..."
                                placeholderTextColor="#aaa"
                                value={search}
                                onChangeText={setSearch}
                                onSubmitEditing={() => { setLoading(true); loadOrgs(); }}
                                returnKeyType="search"
                            />
                        </View>
                    </View>
                </SafeAreaView>
            </View>

            {/* Category filter */}
            <View style={styles.filterRow}>
                <FlatList
                    horizontal
                    data={CATEGORIES}
                    keyExtractor={i => i}
                    showsHorizontalScrollIndicator={false}
                    contentContainerStyle={{ paddingHorizontal: 16, gap: 8 }}
                    renderItem={({ item }) => (
                        <TouchableOpacity
                            style={[styles.filterChip, item === category && styles.filterChipActive]}
                            onPress={() => setCategory(item)}
                        >
                            <Text style={[styles.filterText, item === category && styles.filterTextActive]}>{item}</Text>
                        </TouchableOpacity>
                    )}
                />
            </View>

            {loading ? (
                <ActivityIndicator style={{ marginTop: 60 }} color="#4A6CF7" size="large" />
            ) : (
                <FlatList
                    data={orgs}
                    keyExtractor={item => String(item.id)}
                    renderItem={renderOrg}
                    numColumns={2}
                    columnWrapperStyle={styles.row}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadOrgs(); }} />}
                    ListEmptyComponent={<Text style={styles.empty}>No organizations found.</Text>}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
    header: { backgroundColor: '#4A6CF7', paddingHorizontal: 16, paddingBottom: 16 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 8, marginBottom: 12 },
    backBtn: { padding: 4, marginRight: 8 },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28 },
    headerTitle: { color: '#fff', fontSize: 20, fontWeight: '700' },
    searchRow: { flexDirection: 'row', gap: 10 },
    searchWrap: {
        flex: 1, flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 12, paddingHorizontal: 14, height: 46,
    },
    searchIcon: { fontSize: 16, marginRight: 8 },
    searchInput: { flex: 1, fontSize: 14, color: '#333' },
    filterRow: { paddingVertical: 12 },
    filterChip: {
        paddingHorizontal: 14, paddingVertical: 6,
        borderRadius: 20, backgroundColor: '#fff',
        borderWidth: 1, borderColor: '#e0e0e0',
    },
    filterChipActive: { backgroundColor: '#4A6CF7', borderColor: '#4A6CF7' },
    filterText: { fontSize: 13, color: '#555' },
    filterTextActive: { color: '#fff', fontWeight: '600' },
    list: { paddingHorizontal: 12, paddingBottom: 30 },
    row: { justifyContent: 'space-between', marginBottom: 12, paddingHorizontal: 4 },
    orgCard: {
        backgroundColor: '#fff', borderRadius: 14,
        alignItems: 'center', padding: 16,
        flex: 1, marginHorizontal: 4,
        shadowColor: '#000', shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.07, shadowRadius: 8, elevation: 3,
    },
    orgLogoWrap: { marginBottom: 10 },
    orgLogo: { width: 72, height: 72, borderRadius: 36 },
    orgLogoFallback: { backgroundColor: '#4A6CF7', alignItems: 'center', justifyContent: 'center' },
    orgLogoText: { color: '#fff', fontSize: 28, fontWeight: '700' },
    orgName: { fontSize: 15, fontWeight: '700', color: '#1e2f6e', marginBottom: 4, textAlign: 'center' },
    orgPresident: { fontSize: 12, color: '#4A6CF7', fontWeight: '600', marginBottom: 6, textAlign: 'center' },
    orgMission: { fontSize: 12, color: '#666', lineHeight: 16, marginBottom: 8, textAlign: 'center' },
    viewDetails: { fontSize: 12, color: '#4A6CF7', fontWeight: '600' },
    empty: { textAlign: 'center', marginTop: 60, color: '#888', fontSize: 15 },
});
